<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 10:49
 */
namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use PHPUnit_Framework_TestCase;

class EventFromCdbXmlCommandHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventFromCdbXmlCommandHandler
     */
    protected $eventFromCdbXmlCommandHandler;

    public function setUp()
    {
        $this->eventFromCdbXmlCommandHandler = new EventFromCdbXmlCommandHandler();
    }

    /**
     * @test
     */
    public function it_validates_the_xml_namespace()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/InvalidNamespace.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\UnexpectedNamespaceException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_the_root_element()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/InvalidRootElement.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\UnexpectedRootElementException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }
}
