<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 10:49
 */
namespace CultuurNet\UDB3SilexEntryAPI\CommandHandler;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Event\Commands\Unlabel;
use CultuurNet\UDB3\Event\EventRepository;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LinkType;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\AddLink;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\ApplyTranslation;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\DeleteTranslation;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\MergeLabels;
use CultuurNet\UDB3SilexEntryAPI\Event\Commands\UpdateEventFromCdbXml;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SuspiciousContentException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooManyItemsException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedRootElementException;
use CultuurNet\UDB3SilexEntryAPI\SizeLimitedEventXmlString;
use ValueObjects\String\String;

class EntryAPIEventCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var String
     */
    protected $id;

    /**
     * @var String
     */
    protected $namespaceUri;

    /**
     * @var EventCreatedFromCdbXml
     */
    protected $eventCreated;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->id = new String('004aea08-e13d-48c9-b9eb-a18f20e6d44e');
        $xml = $this->loadXmlString('ValidWithCdbid.xml');

        $this->namespaceUri = new String(
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL'
        );

        $this->eventCreated = new EventCreatedFromCdbXml(
            $this->id,
            $xml,
            $this->namespaceUri
        );
    }

    /**
     * @inheritdoc
     */
    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        return new EntryAPIEventCommandHandler(
            new EventRepository($eventStore, $eventBus)
        );
    }

    /**
     * @param string $file
     * @return SizeLimitedEventXmlString
     */
    protected function loadXmlString($file)
    {
        return new SizeLimitedEventXmlString(
            file_get_contents(__DIR__ . '/' . $file)
        );
    }

    /**
     * @test
     */
    public function it_validates_the_xml_namespace()
    {
        $xml = $this->loadXmlString('InvalidNamespace.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedNamespaceException::class);

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_the_xml_namespace_for_update()
    {
        $xml = $this->loadXmlString('InvalidNamespace.xml');

        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedNamespaceException::class);

        $this->scenario->when($updateEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_the_root_element()
    {
        $xml = $this->loadXmlString('InvalidRootElement.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedRootElementException::class);

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_the_root_element_for_update()
    {
        $xml = $this->loadXmlString('InvalidRootElement.xml');

        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(UnexpectedRootElementException::class);

        $this->scenario->when($updateEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_against_the_xml_schema()
    {
        $xml = $this->loadXmlString('InvalidSchemaTitleMissing.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(SchemaValidationException::class);

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_against_the_xml_schema_for_update()
    {
        $xml = $this->loadXmlString('InvalidSchemaTitleMissing.xml');

        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(SchemaValidationException::class);

        $this->scenario->when($updateEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_accepts_valid_cdbxml()
    {
        $id = new String('foo');
        $xml = $this->loadXmlString('Valid.xml');
        $addEventFromCdbXml = new AddEventFromCdbXml($id, $xml);

        $this->scenario
            ->when($addEventFromCdbXml)
            ->then(
                [
                    new EventCreatedFromCdbXml(
                        $id,
                        $xml,
                        $this->namespaceUri
                    )
                ]
            );
    }

    /**
     * @test
     */
    public function it_accepts_valid_cdbxml_for_update()
    {
        $xml = $this->loadXmlString('Valid.xml');

        $updateEventFromCdbXml = new UpdateEventFromCdbXml($this->id, $xml);

        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->eventCreated
                ]
            )
            ->when($updateEventFromCdbXml)
            ->then(
                [
                    new EventUpdatedFromCdbXml(
                        $this->id,
                        $xml,
                        $this->namespaceUri
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_validates_too_many_events()
    {
        $xml = $this->loadXmlString('TooManyEvents.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(
            TooManyItemsException::class
        );

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_no_event()
    {
        $xml = $this->loadXmlString('NoEventAtAll.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(
            ElementNotFoundException::class
        );

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_when_there_is_no_element_at_all()
    {
        $xml = $this->loadXmlString('NoEventButActor.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(
            ElementNotFoundException::class
        );

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_empty_xml()
    {
        $xml = $this->loadXmlString('Empty.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(
            ElementNotFoundException::class
        );

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_validates_suspicious_content()
    {
        $xml = $this->loadXmlString('ScriptTag.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($this->id, $xml);

        $this->setExpectedException(
            SuspiciousContentException::class
        );

        $this->scenario->when($addEventFromCdbXml);
    }

    /**
     * @test
     */
    public function it_creates_an_event_when_posting_xml_without_a_cdbid()
    {
        $id = new String('foo');
        $xml = $this->loadXmlString('Valid.xml');

        $addEventFromCdbXml = new AddEventFromCdbXml($id, $xml);

        $this->scenario
            ->when($addEventFromCdbXml)
            ->then(
                [
                    new EventCreatedFromCdbXml(
                        $id,
                        $xml,
                        $this->namespaceUri
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_merges_labels()
    {
        $labels = new LabelCollection(
            [
                new Label('keyword1', false),
                new Label('keyword2', true),
            ]
        );

        $mergeLabels = new MergeLabels(
            $this->id,
            $labels
        );

        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->eventCreated
                ]
            )
            ->when($mergeLabels)
            ->then(
                [
                    new LabelsMerged(
                        $this->id,
                        $labels
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_applies_a_translation()
    {
        $title = new String('Dizorkestra en concert');
        $shortDescription = new String(
            'Concert Dizôrkestra, un groupe qui.'
        );
        $longDescription = new String(
            'Concert Dizôrkestra, un groupe qui se montre inventif.'
        );

        $applyTranslation = new ApplyTranslation(
            $this->id,
            new Language('fr'),
            $title,
            $shortDescription,
            $longDescription
        );

        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->eventCreated,
                ]
            )
            ->when($applyTranslation)
            ->then(
                [
                    new TranslationApplied(
                        $this->id,
                        new Language('fr'),
                        $title,
                        $shortDescription,
                        $longDescription
                    )
                ]
            );
    }

    /**
     * @test
     */
    public function it_deletes_a_translation()
    {
        $translationApplied = new TranslationApplied(
            $this->id,
            new Language('fr'),
            new String('Vive la monde')
        );

        $deleteTranslation = new DeleteTranslation(
            $this->id,
            new Language('fr')
        );

        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->eventCreated,
                    $translationApplied,
                ]
            )
            ->when($deleteTranslation)
            ->then(
                [
                    new TranslationDeleted(
                        $this->id,
                        new Language('fr')
                    )
                ]
            );
    }

    /**
     * @test
     */
    public function it_unlabels()
    {
        $label = new Label('classic rock');

        $unlabel = new Unlabel($this->id, $label);

        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->eventCreated,
                    new EventWasLabelled(
                        $this->id->toNative(),
                        $label
                    ),
                ]
            )
            ->when($unlabel)
            ->then(
                [
                    new Unlabelled(
                        $this->id->toNative(),
                        $label
                    )
                ]
            );
    }

    /**
     * @test
     */
    public function it_adds_a_link()
    {
        $addLink = new AddLink(
            new String('004aea08-e13d-48c9-b9eb-a18f20e6d44e'),
            new Language('fr'),
            new String('http://cultuurnet.be'),
            new LinkType('roadmap'),
            null,
            null,
            null,
            null
        );

        $this->eventRepository->expects($this->once())
            ->method('load')
            ->with('004aea08-e13d-48c9-b9eb-a18f20e6d44e');

        $this->eventRepository->expects($this->once())
            ->method('save');

        $this->eventFromCdbXmlCommandHandler->handle($addLink);
    }
}
