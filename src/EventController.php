<?php

namespace CultuurNet\UDB3SilexEntryAPI;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\Entry\Rsp;
use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Event\Commands\DeleteLabel;
use CultuurNet\UDB3\Event\EventNotFoundException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\XMLSyntaxException;
use CultuurNet\UDB3SilexEntryAPI\CommandHandler\EntryAPIEventCommandHandler;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddCollaborationLink;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\ApplyTranslation;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\DeleteTranslation;
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
use ValueObjects\Web\Url;

class EventController
{
    /**
     * @var RepositoryInterface
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
                $rsp = rsp::error(
                    'UnexpectedFailure',
                    'Content-Type is not x-www-form-urlencoded.'
                );
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
                $shortDescription = new String(
                    $request->request->get('shortdescription')
                );
            }

            $longDescription = null;
            if ($request->request->has('longdescription')) {
                $longDescription = new String(
                    $request->request->get('longdescription')
                );
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

            return $this->createInfoResponseForEvent(
                $cdbid,
                'TranslationCreated'
            );
        };

        return $this->processEventRequest($callback);
    }

    public function deleteTranslation(Request $request, $cdbid)
    {
        $callback = function () use ($request, $cdbid) {
            $repository = $this->eventRepository;

            if ($request->query->has('lang')) {
                $language = strtolower($request->query->get('lang'));
            } else {
                throw new InvalidArgumentException(
                    'Language code is required.'
                );
            }

            $eventId = new String($cdbid);

            $command = new DeleteTranslation(
                $eventId,
                new Language($language)
            );

            $commandHandler = new EntryAPIEventCommandHandler($repository);
            $commandHandler->handle($command);

            return $this->createInfoResponseForEvent(
                $cdbid,
                'TranslationWithdrawn'
            );
        };

        return $this->processEventRequest($callback);
    }

    /**
     * @param string $cdbid
     * @param string $code
     * @return Rsp
     */
    protected function createInfoResponseForEvent($cdbid, $code)
    {
        $link = $this->entryapiLinkBaseUrl . $cdbid;
        $rsp = new Rsp('0.1', Rsp::LEVEL_INFO, $code, $link, null);

        return $rsp;
    }

    /**
     * @param Request $request
     * @param string $cdbid
     * @return Response
     */
    public function deleteKeyword(Request $request, $cdbid)
    {
        $label = new Label($request->query->get('keyword'));

        $callback = function () use ($cdbid, $label) {
            $command = new DeleteLabel($cdbid, $label);

            $repository = $this->eventRepository;

            $commandHandler = new EntryAPIEventCommandHandler($repository);
            $commandHandler->handle($command);

            return $this->createInfoResponseForEvent(
                $cdbid,
                'KeywordWithdrawn'
            );
        };

        return $this->processEventRequest($callback);
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @return Response
     */
    public function addLink(Request $request, $cdbid)
    {
        $callback = function () use ($request, $cdbid) {
            $repository = $this->eventRepository;

            if ($request->getContentType() !== 'form') {
                $rsp = rsp::error('UnexpectedFailure', 'Content-Type is not x-www-form-urlencoded.');
                return $rsp;
            }

            $required = [
                'lang' => 'Language code',
                'subbrand' => 'Sub-brand',
                'description' => 'Description',
                'linktype' => 'Link type',
                'plaintext' => 'Plain text',
            ];

            foreach ($required as $requiredProperty => $humanReadable) {
                if (!$request->request->get($requiredProperty)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            '%s is required.',
                            $humanReadable
                        )
                    );
                }
            }

            $type = strtolower($request->request->get('linktype'));

            // At this point only collaboration "links" are supported.
            if ($type !== 'collaboration') {
                throw new InvalidArgumentException(
                    'Link type should be "collaboration". Any other type is not supported.'
                );
            }

            $eventId = new String($cdbid);

            $language = new Language(
                strtolower($request->request->get('lang'))
            );

            $collaborationData = new CollaborationData(
                new String($request->request->get('subbrand')),
                new String($request->request->get('plaintext'))
            );

            if ($request->request->has('title')) {
                $collaborationData = $collaborationData
                    ->withTitle(
                        new String($request->request->get('title'))
                    );
            }

            if ($request->request->has('copyright')) {
                $collaborationData = $collaborationData
                    ->withCopyright(
                        new String($request->request->get('copyright'))
                    );
            }

            if ($request->request->has('link')) {
                $collaborationData = $collaborationData
                    ->withLink(
                        Url::fromNative($request->request->get('link'))
                    );
            }

            $description = json_decode(
                $request->request->get('description')
            );

            if (is_null($description)) {
                throw new InvalidArgumentException(
                    'Description is not a valid json string.'
                );
            }

            if (!empty($description->text)) {
                $collaborationData = $collaborationData
                    ->withText(
                        new String($description->text)
                    );
            }

            if (!empty($description->keyword)) {
                $collaborationData = $collaborationData
                    ->withKeyword(
                        new String($description->keyword)
                    );
            }

            if (!empty($description->article)) {
                $collaborationData = $collaborationData
                    ->withArticle(
                        new String($description->article)
                    );
            }

            if (!empty($description->image)) {
                $collaborationData = $collaborationData
                    ->withImage(
                        new String($description->image)
                    );
            }

            $command = new AddCollaborationLink(
                $eventId,
                $language,
                $collaborationData
            );

            $commandHandler = new EntryAPIEventCommandHandler($repository);
            $commandHandler->handle($command);

            $link = $this->entryapiLinkBaseUrl . $cdbid;
            $rsp = new Rsp('0.1', 'INFO', 'LinkCreated', $link, null);

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
        } catch (EventNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $rsp = rsp::error('NotFound', 'Resource not found');
        } catch (\Exception $e) {
            $rsp = rsp::error('UnexpectedFailure', $e->getMessage());
        }

        return $this->createResponse($rsp, $status);
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
