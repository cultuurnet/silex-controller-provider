<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 02/10/15
 * Time: 21:05
 */

namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\TooManyItemsException;
use CultuurNet\UDB3SilexEntryAPI\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\UnexpectedRootElementException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventFromCdbXmlCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
    }
}
