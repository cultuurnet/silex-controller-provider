<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 10/11/15
 * Time: 11:49
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\Entry\Rsp;
use CultuurNet\UDB3\UDB2\EventRepository;
use CultuurNet\UDB3\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String;

class EventControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var string
     */
    protected $entryapiLinkBaseUrl;

    /**
     * @var EventController
     */
    protected $controller;

    public function setUp()
    {
        $this->eventRepository = $this->getMock(RepositoryInterface::class);

        $this->id = new String('test123');
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/CommandHandler/ValidWithCdbid.xml'));
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
        $this->entryapiLinkBaseUrl = 'http://www.uitdatabank.be/api/v3/event/';

        $this->controller = new EventController($this->eventRepository, $this->entryapiLinkBaseUrl);
    }

    /**
     * @test
     */
    public function it_can_respond_to_a_translation()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = new Request();
        $request->create('/event/someId/translations', 'post', [], [], [], [], []);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $request->request->set('lang', 'fr');
        $request->request->set('title', 'Titre');
        $request->request->set('shortdescription', 'Une courte description.');
        $request->request->set('longdescription', 'Une longue description.');

        $response = $this->controller->translate($request, $cdbid);

        $this->assertEquals(200, $response->getStatusCode());

        $link = $this->entryapiLinkBaseUrl . $cdbid;
        $rsp = new Rsp('0.1', 'INFO', 'TranslationCreated', $link, null);

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_throw_an_error_if_content_type_is_not_form()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = new Request();
        $request->create('/event/someId/translations', 'post', [], [], [], [], []);
        //$request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $request->request->set('lang', 'fr');
        $request->request->set('title', 'Titre');
        $request->request->set('shortdescription', 'Une courte description.');
        $request->request->set('longdescription', 'Une longue description.');

        $response = $this->controller->translate($request, $cdbid);

        $this->assertEquals(400, $response->getStatusCode());

        $rsp = $rsp = rsp::error('UnexpectedFailure', 'Content-Type is not x-www-form-urlencoded.');

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }
}
