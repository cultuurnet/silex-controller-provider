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
use CultuurNet\UDB3SilexEntryAPI\InvalidCdbXmlException;
use CultuurNet\UDB3SilexEntryAPI\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\UnexpectedRootElementException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventFromCdbXmlCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function handleAddEventFromCdbXml(AddEventFromCdbXml $addEventFromCdbXml)
    {
        $xml = $addEventFromCdbXml->getXml();
        $dom = new \DOMDocument();
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
    }
}
