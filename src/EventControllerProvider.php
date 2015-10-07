<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 30.09.15
 * Time: 15:43
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\Entry\Rsp;
use CultuurNet\UDB3\Event\EventCommandHandler;
use CultuurNet\UDB3\XMLSyntaxException;
use CultuurNet\UDB3SilexEntryAPI\CommandHandler\EventFromCdbXmlCommandHandler;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SizeLimitedXmlString;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SuspiciousContentException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooLargeException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooManyItemsException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedRootElementException;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\String\String;

class EventControllerProvider implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return Response
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->post(
            '/event',
            function (
                Request $request,
                Application $app,
                UuidGeneratorInterface $uuidGenerator,
                RepositoryInterface $repositoryInterface
            ) {
                try {
                    if ($request->getContentType() !== 'xml') {
                        return new Response('', Response::HTTP_BAD_REQUEST);
                    }

                    $xml = new SizeLimitedXmlString($request->getContent());
                    $id = $uuidGenerator->generate();
                    $eventId = new String($id);

                    $command = new AddEventFromCdbXml($eventId, $xml);

                    $commandHandler = new EventFromCdbXmlCommandHandler($repositoryInterface);
                    $commandHandler->handle($command);
                    $rsp = new Rsp('0.1', 'INFO', 'ItemCreated', null, null);

                } catch (TooLargeException $e) {
                    $rsp = rsp::error('FileSizeTooLarge', $e->getMessage());
                } catch (XMLSyntaxException $e) {
                    $rsp = rsp::error('XmlSyntaxError', $e->getMessage());
                } catch (ElementNotFoundException $e) {
                    $rsp = rsp::error('ElementNotFoundError', $e->getMessage());
                } catch (UnexpectedNamespaceException $e) {
                    $rsp = rsp::error('XmlSyntaxError', $e->getMessage());
                } catch (UnexpectedRootElementException $e) {
                    $rsp = rsp::error('XmlSyntaxError', $e->getMessage());
                } catch (SchemaValidationException $e) {
                    $rsp = rsp::error('XmlSyntaxError', $e->getMessage());
                } catch (TooManyItemsException $e) {
                    $rsp = rsp::error('TooManyItems', $e->getMessage());
                } catch (SuspiciousContentException $e) {
                    $rsp = rsp::error('SuspectedContent', $e->getMessage());
                } catch (\Exception $e) {
                    $rsp = rsp::error('UnexpectedFailure', $e->getMessage());
                } finally {
                    return $this->createResponse($rsp);
                }
            }
        );

        return $controllers;
    }

    /**
     * @param Rsp $rsp
     * @return Response
     */
    private function createResponse(Rsp $rsp)
    {
        $headers = array('Content-Type'=>'application/xml');
        $xml = $rsp->toXml();

        if ($rsp->isError()) {
            $level = Response::HTTP_BAD_REQUEST;
        } else {
            $level = Response::HTTP_OK;
        }

        return new Response($xml, $level, $headers);
    }
}
