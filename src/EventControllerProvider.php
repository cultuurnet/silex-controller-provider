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
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\XMLSyntaxException;
use CultuurNet\UDB3SilexEntryAPI\CommandHandler\EventFromCdbXmlCommandHandler;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\UpdateEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\SizeLimitedXmlString;
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
            function (Request $request, Application $app) {
                $callback = function () use ($request, $app) {
                    $repository = $app['event_repository'];
                    $uuidGenerator = new \Broadway\UuidGenerator\Rfc4122\Version4Generator();
                    if ($request->getContentType() !== 'xml') {
                        $rsp = rsp::error('UnexpectedFailure', 'Content-Type is not XML.');
                        return $this->createResponse($rsp);
                    }

                    $xml = new SizeLimitedEventXmlString($request->getContent());

                    $id = $uuidGenerator->generate();
                    $eventId = new String($id);

                    $command = new AddEventFromCdbXml($eventId, $xml);

                    $commandHandler = new EventFromCdbXmlCommandHandler($repository);
                    $commandHandler->handle($command);
                    $link = $app['entryapi.link_base_url'] . $eventId;
                    $rsp = new Rsp('0.1', 'INFO', 'ItemCreated', $link, null);
                    return $rsp;
                };

                return $this->processEventRequest($callback);
            }
        );

        $controllers->put(
            '/event/{cdbid}',
            function (Request $request, Application $app, $cdbid) {
                $callback = function () use ($request, $app, $cdbid) {
                    $repository = $app['event_repository'];
                    if ($request->getContentType() !== 'xml') {
                        $rsp = rsp::error('UnexpectedFailure', 'Content-Type is not XML.');
                        return $this->createResponse($rsp);
                    }

                    $xml = new SizeLimitedEventXmlString($request->getContent());
                    $eventId = new String($cdbid);

                    $command = new UpdateEventFromCdbXml($eventId, $xml);

                    $commandHandler = new EventFromCdbXmlCommandHandler($repository);
                    $commandHandler->handle($command);
                    $link = $app['entryapi.link_base_url'] . $eventId;
                    $rsp = new Rsp('0.1', 'INFO', 'ItemModified', $link, null);
                    return $rsp;
                };

                return $this->processEventRequest($callback);
            }
        );

        return $controllers;
    }

    private function processEventRequest($callback)
    {
        $status = null;

        try {
            $rsp = $callback();

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
        } catch (\CultuurNet\UDB3\UDB2\EventNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $rsp = rsp::error('NotFound', 'Resource not found');
        } catch (EventNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $rsp = rsp::error('NotFound', 'Resource not found');
        } catch (\Exception $e) {
            $rsp = rsp::error('UnexpectedFailure', $e->getMessage());
        } finally {
            return $this->createResponse($rsp, $status);
        }
    }

    /**
     * @param Rsp $rsp
     * @return Response
     */
    private function createResponse(Rsp $rsp, $status = null)
    {
        $headers = array('Content-Type'=>'application/xml');
        $xml = $rsp->toXml();

        if (null === $status) {
            $status = $this->statusForRsp($rsp);
        }

        return new Response($xml, $status, $headers);
    }

    /**
     * @param Rsp $rsp
     * @return int
     */
    private function statusForRsp(Rsp $rsp)
    {
        if ($rsp->isError()) {
            return Response::HTTP_BAD_REQUEST;
        }

        return Response::HTTP_OK;
    }
}
