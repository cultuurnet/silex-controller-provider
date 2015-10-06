<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 10:49
 */
namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use CultuurNet\UDB3SilexEntryAPI\SizeLimitedXmlString;
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

    /**
     * @test
     */
    public function it_validates_against_the_xml_schema()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/InvalidSchemaTitleMissing.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\SchemaValidationException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_accepts_valid_cdbxml()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/Valid.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_too_many_events()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/TooManyEvents.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\TooManyItemsException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_no_event()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/NoEventButActor.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\ElementNotFoundException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_empty_xml()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/Empty.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\ElementNotFoundException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_suspicious_content()
    {
        $xml = new \CultuurNet\UDB3\XmlString(file_get_contents(__DIR__ . '/ScriptTag.xml'));
        $addEventFromCdbXml = new \CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml($xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\SuspiciousContentException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }
}
