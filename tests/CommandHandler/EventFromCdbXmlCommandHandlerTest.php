<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 10:49
 */
namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\UpdateEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\EventUpdatedException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedRootElementException;
use CultuurNet\UDB3SilexEntryAPI\SizeLimitedEventXmlString;
use PHPUnit_Framework_TestCase;
use ValueObjects\String\String;

class EventFromCdbXmlCommandHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventRepository;

    /**
     * @var EventFromCdbXmlCommandHandler
     */
    protected $eventFromCdbXmlCommandHandler;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var String
     */
    protected $id;

    public function setUp()
    {
        $this->eventRepository = $this->getMock(RepositoryInterface::class);

        $this->id = new String('test123');
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/ValidWithCdbid.xml'));
        $namespaceUri = new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL');

        $event = Event::createFromCdbXml(
            $this->id,
            $xml,
            $namespaceUri
        );

        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';

        $this->eventRepository->expects($this->any())
            ->method('load')
            ->with($cdbid)
            ->willReturn($event);

        $this->eventFromCdbXmlCommandHandler = new EventFromCdbXmlCommandHandler(
            $this->eventRepository
        );
    }

    /**
     * @test
     */
    public function it_validates_the_xml_namespace()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/InvalidNamespace.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedNamespaceException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_the_xml_namespace_for_update()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/InvalidNamespace.xml'));
        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedNamespaceException::class);

        $this->eventFromCdbXmlCommandHandler->handle($updateEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_the_root_element()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/InvalidRootElement.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedRootElementException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_the_root_element_for_update()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/InvalidRootElement.xml'));
        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedRootElementException::class);

        $this->eventFromCdbXmlCommandHandler->handle($updateEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_against_the_xml_schema()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/InvalidSchemaTitleMissing.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(SchemaValidationException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_against_the_xml_schema_for_update()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/InvalidSchemaTitleMissing.xml'));
        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(SchemaValidationException::class);

        $this->eventFromCdbXmlCommandHandler->handle($updateEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_accepts_valid_cdbxml()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/Valid.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_accepts_valid_cdbxml_for_update()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/Valid.xml'));
        $this->id = new String('004aea08-e13d-48c9-b9eb-a18f20e6d44e');
        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->eventFromCdbXmlCommandHandler->handle($updateEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_too_many_events()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/TooManyEvents.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\Exceptions\TooManyItemsException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_no_event()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/NoEventButActor.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_empty_xml()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/Empty.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_suspicious_content()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/ScriptTag.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(\CultuurNet\UDB3SilexEntryAPI\Exceptions\SuspiciousContentException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_updates_an_event_when_posting_xml_with_a_cdbid()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/ValidWithCdbid.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->eventRepository->expects($this->once())
            ->method('load')
            ->with('004aea08-e13d-48c9-b9eb-a18f20e6d44e');

        $this->eventRepository->expects($this->once())
            ->method('save');

        $this->setExpectedException(EventUpdatedException::class);

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_creates_an_event_when_posting_xml_without_a_cdbid()
    {
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/Valid.xml'));
        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->eventRepository->expects($this->never())
            ->method('load');

        $this->eventRepository->expects($this->once())
            ->method('save');

        $this->eventFromCdbXmlCommandHandler->handle($addEventFromCdbXml);
    }
}
