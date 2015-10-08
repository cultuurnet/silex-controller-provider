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
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Event\DefaultEventEditingService;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SuspiciousContentException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooManyItemsException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedRootElementException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ValueObjects\String\String;

class EventFromCdbXmlCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RepositoryInterface
     */
    protected $eventRepository;

    public function __construct(
        RepositoryInterface $eventRepository
    ) {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param AddEventFromCdbXml $addEventFromCdbXml
     */
    public function handleAddEventFromCdbXml(AddEventFromCdbXml $addEventFromCdbXml)
    {
        libxml_use_internal_errors(true);
        $xml = $addEventFromCdbXml->getXml();
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);
        $namespaceURI = $dom->documentElement->namespaceURI;
        $validNamespaces = array('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL');

        if (!in_array($namespaceURI, $validNamespaces)) {
            throw new UnexpectedNamespaceException($namespaceURI, $validNamespaces);
        }

        $localName = $dom->documentElement->localName;
        $expectedLocalName = 'cdbxml';

        if ($localName !== $expectedLocalName) {
            throw new UnexpectedRootElementException($localName, $expectedLocalName);
        }

        if (!$dom->schemaValidate(__DIR__ . '/../CdbXmlSchemes/CdbXSD3.3.xsd')) {
            throw new SchemaValidationException($namespaceURI);
        }

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

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cdb', $namespaceURI);
        $longDescriptions = $xpath->query('//cdb:longdescription');

        if ($longDescriptions->length > 0) {
            /** @var \DOMElement $longDescription */
            foreach ($longDescriptions as $longDescription) {
                if (stripos($longDescription->textContent, '<script>') !== false) {
                    throw new SuspiciousContentException();
                }
            }
        }

        $cdbXmlNamespaceUri = new String($namespaceURI);
        $event = Event::createFromCdbXml(
            $addEventFromCdbXml->getEventId(),
            $xml,
            $cdbXmlNamespaceUri
        );

        $this->eventRepository->save($event);
    }
}
