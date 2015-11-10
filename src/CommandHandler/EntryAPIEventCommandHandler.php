<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 02/10/15
 * Time: 21:05
 */

namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\MergeLabels;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\UpdateEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\EventUpdatedException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SuspiciousContentException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooManyItemsException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedRootElementException;
use DOMDocument;
use DOMElement;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ValueObjects\String\String;

class EntryAPIEventCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var string[]
     */
    protected $validNamespaces;

    public function __construct(RepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->validNamespaces = [
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL' => __DIR__ . '/../CdbXmlSchemes/CdbXSD3.3.xsd',
        ];
    }

    /**
     * @param AddEventFromCdbXml $addEventFromCdbXml
     * @throws UnexpectedNamespaceException
     * @throws UnexpectedRootElementException
     * @throws SchemaValidationException
     * @throws ElementNotFoundException
     * @throws SuspiciousContentException
     * @throws EventUpdatedException
     */
    public function handleAddEventFromCdbXml(
        AddEventFromCdbXml $addEventFromCdbXml
    ) {
        libxml_use_internal_errors(true);
        $xml = $addEventFromCdbXml->getXml();
        $dom = $this->loadDOM($xml);

        $namespaceURI = $dom->documentElement->namespaceURI;
        $element = $this->getEventElement($dom);

        $this->guardDescriptions($dom);

        $cdbXmlNamespaceUri = new String($namespaceURI);

        /** @var Event $event */
        $event = null;
        $cdbid = $element->getAttribute('cdbid');
        if (!empty($cdbid)) {
            $event = $this->eventRepository->load($cdbid);
        }

        if (!empty($event)) {
            $event->updateFromCdbXml(
                new String($cdbid),
                $xml,
                $cdbXmlNamespaceUri
            );

            $this->eventRepository->save($event);

            throw new EventUpdatedException($cdbid);
        } else {
            $event = Event::createFromCdbXml(
                $addEventFromCdbXml->getEventId(),
                $xml,
                $cdbXmlNamespaceUri
            );

            $this->eventRepository->save($event);
        }
    }

    /**
     * @param UpdateEventFromCdbXml $updateEventFromCdbXml
     */
    public function handleUpdateEventFromCdbXml(
        UpdateEventFromCdbXml $updateEventFromCdbXml
    ) {
        libxml_use_internal_errors(true);
        $xml = $updateEventFromCdbXml->getXml();
        $dom = $this->loadDOM($xml);

        $this->getEventElement($dom);

        $this->guardDescriptions($dom);

        /** @var Event $event */
        $event = $this->eventRepository->load(
            $updateEventFromCdbXml->getEventId()->toNative()
        );

        $cdbXmlNamespaceUri = new String($dom->documentElement->namespaceURI);
        $event->updateFromCdbXml(
            $updateEventFromCdbXml->getEventId(),
            $xml,
            $cdbXmlNamespaceUri
        );

        $this->eventRepository->save($event);
    }

    /**
     * @param MergeLabels $applyLabels
     */
    public function handleMergeLabels(MergeLabels $applyLabels)
    {
        /** @var Event $event */
        $event = $this->eventRepository->load(
            $applyLabels->getEventId()->toNative()
        );

        $event->mergeLabels($applyLabels->getLabels());

        $this->eventRepository->save($event);
    }


    /**
     * @param DOMDocument $dom
     * @throws SuspiciousContentException
     */
    private function guardDescriptions(DOMDocument $dom)
    {
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cdb', $dom->documentElement->namespaceURI);
        $longDescriptions = $xpath->query('//cdb:longdescription');

        if ($longDescriptions->length > 0) {
            /** @var \DOMElement $longDescription */
            foreach ($longDescriptions as $longDescription) {
                if ($this->containsScriptTag($longDescription)) {
                    throw new SuspiciousContentException();
                }
            }
        }
    }

    /**
     * @param DOMElement $element
     * @return bool
     */
    private function containsScriptTag(DOMElement $element)
    {
        return stripos($element->textContent, '<script>') !== false;
    }

    /**
     * @param DOMDocument $dom
     * @return \DOMElement
     * @throws ElementNotFoundException
     * @throws TooManyItemsException
     */
    private function getEventElement(DOMDocument $dom)
    {
        $namespaceURI = $dom->documentElement->namespaceURI;
        $childNodes = $dom->documentElement->childNodes;
        $element = $childNodes->item(0);

        $expectedElementLocalName = 'event';
        $expectedElement = $namespaceURI . ":" . $expectedElementLocalName;

        if ($element !== null) {
            $elementLocalName = $element->localName;
            $elementNamespaceURI = $element->namespaceURI;

            $elementFound = $elementNamespaceURI . ":" . $elementLocalName;

            if ($elementNamespaceURI !== $namespaceURI) {
                throw new ElementNotFoundException($expectedElement, $elementFound);
            }

            if ($elementLocalName !== $expectedElementLocalName) {
                throw new ElementNotFoundException($expectedElement, $elementFound);
            }
        } else {
            throw new ElementNotFoundException($expectedElement);
        }

        if ($childNodes->length > 1) {
            throw new TooManyItemsException();
        }

        return $element;
    }

    /**
     * @param string $xml
     * @return DOMDocument
     * @throws SchemaValidationException
     * @throws UnexpectedNamespaceException
     * @throws UnexpectedRootElementException
     */
    private function loadDOM($xml)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        $namespaceURI = $dom->documentElement->namespaceURI;

        if (!array_key_exists($namespaceURI, $this->validNamespaces)) {
            throw new UnexpectedNamespaceException(
                $namespaceURI,
                $this->validNamespaces
            );
        }
        $schema = $this->validNamespaces[$namespaceURI];

        $localName = $dom->documentElement->localName;
        $expectedLocalName = 'cdbxml';

        if ($localName !== $expectedLocalName) {
            throw new UnexpectedRootElementException(
                $localName,
                $expectedLocalName
            );
        }

        if (!$dom->schemaValidate($schema)) {
            throw new SchemaValidationException($namespaceURI);
        }

        return $dom;
    }
}
