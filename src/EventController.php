<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 10/11/15
 * Time: 11:27
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\Entry\Rsp;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\UDB2\EventRepository;
use CultuurNet\UDB3\XMLSyntaxException;
use CultuurNet\UDB3SilexEntryAPI\CommandHandler\EntryAPIEventCommandHandler;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\ApplyTranslation;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SuspiciousContentException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooLargeException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooManyItemsException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedRootElementException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\String\String;

class EventController
{
    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var string
     */
    protected $entryapiLinkBaseUrl;

    public function __construct(RepositoryInterface $eventRepository, $entryapiLinkBaseUrl)
    {
        $this->eventRepository = $eventRepository;
        $this->entryapiLinkBaseUrl = $entryapiLinkBaseUrl;
    }

    public function translate(Request $request, $cdbid)
    {
        $callback = function () use ($request, $cdbid) {
            $repository = $this->eventRepository;

            if ($request->getContentType() !== 'form') {
                $rsp = rsp::error('UnexpectedFailure', 'Content-Type is not x-www-form-urlencoded.');
                return $rsp;
            }

            if ($request->request->has('lang')) {
                $language = strtolower($request->request->get('lang'));
            } else {
                throw new InvalidArgumentException(
                    'Language code is required.'
                );
            }

            $title = null;
            if ($request->request->has('title')) {
                $title = new String($request->request->get('title'));
            }

            $shortDescription = null;
            if ($request->request->has('shortdescription')) {
                $shortDescription = new String($request->request->get('shortdescription'));
            }

            $longDescription = null;
            if ($request->request->has('longdescription')) {
                $longDescription = new String($request->request->get('longdescription'));
            }

            $eventId = new String($cdbid);

            $command = new ApplyTranslation(
                $eventId,
                new Language($language),
                $title,
                $shortDescription,
                $longDescription
            );

            $commandHandler = new EntryAPIEventCommandHandler($repository);
            $commandHandler->handle($command);
            $link = $this->entryapiLinkBaseUrl . $cdbid;
            $rsp = new Rsp('0.1', 'INFO', 'TranslationCreated', $link, null);
            return $rsp;
        };

        return $this->processEventRequest($callback);
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
