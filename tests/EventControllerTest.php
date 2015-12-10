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
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\XMLSyntaxException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\ElementNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SchemaValidationException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\SuspiciousContentException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooLargeException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooManyItemsException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\UnexpectedRootElementException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\String\String;

class EventControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $request = Request::create('/event/someId/translations', Request::METHOD_POST);
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
        $request = Request::create('/event/someId/translations', Request::METHOD_POST);
        $request->headers->set('Content-Type', 'text/json');
        $request->request->set('lang', 'fr');
        $request->request->set('title', 'Titre');
        $request->request->set('shortdescription', 'Une courte description.');
        $request->request->set('longdescription', 'Une longue description.');

        $response = $this->controller->translate($request, $cdbid);

        $this->assertEquals(400, $response->getStatusCode());

        $rsp = $rsp = rsp::error('UnexpectedFailure', 'Content-Type is not x-www-form-urlencoded.');

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_language_is_not_provided_for_a_translation()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = Request::create('/event/someId/translations', Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $request->request->set('title', 'Titre');
        $request->request->set('shortdescription', 'Une courte description.');
        $request->request->set('longdescription', 'Une longue description.');

        $response = $this->controller->translate($request, $cdbid);

        $this->assertEquals(400, $response->getStatusCode());

        $rsp = $rsp = rsp::error('UnexpectedFailure', 'Language code is required.');

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_respond_to_a_title_translation()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = Request::create('/event/someId/translations', Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $request->request->set('lang', 'fr');
        $request->request->set('title', 'Titre');

        $response = $this->controller->translate($request, $cdbid);

        $this->assertEquals(200, $response->getStatusCode());

        $link = $this->entryapiLinkBaseUrl . $cdbid;
        $rsp = new Rsp('0.1', 'INFO', 'TranslationCreated', $link, null);

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_respond_to_a_translation_deletion()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = Request::create('/event/someId/translations', Request::METHOD_DELETE);
        $request->query->set('lang', 'fr');

        $response = $this->controller->deleteTranslation($request, $cdbid);

        $this->assertEquals(200, $response->getStatusCode());

        $link = $this->entryapiLinkBaseUrl . $cdbid;
        $rsp = new Rsp('0.1', 'INFO', 'TranslationWithdrawn', $link, null);

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_respond_to_a_link_addition()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = new Request();
        $request->create('/event/someId/links', 'post', [], [], [], [], []);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $request->request->set('lang', 'fr');
        $request->request->set('link', 'http://cultuurnet.be');
        $request->request->set('linktype', 'collaboration');

        $response = $this->controller->addLink($request, $cdbid);

        $this->assertEquals(200, $response->getStatusCode());

        $link = $this->entryapiLinkBaseUrl . $cdbid;
        $rsp = new Rsp('0.1', 'INFO', 'LinkCreated', $link, null);

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_link_type_is_invalid()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';

        $request = new Request();
        $request->create('/event/someId/links', 'post', [], [], [], [], []);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $request->request->set('lang', 'fr');
        $request->request->set('link', 'http://cultuurnet.be');
        $request->request->set('linktype', 'roadmap');

        $response = $this->controller->addLink($request, $cdbid);

        $this->assertEquals(400, $response->getStatusCode());

        $rsp = $rsp = rsp::error('UnexpectedFailure', 'Unknown value \'roadmap\'');

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_language_is_not_provided_for_a_translation_deletion()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = Request::create('/event/someId/translations', Request::METHOD_DELETE);

        $response = $this->controller->deleteTranslation($request, $cdbid);

        $this->assertEquals(400, $response->getStatusCode());

        $rsp = $rsp = rsp::error('UnexpectedFailure', 'Language code is required.');

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_respond_to_a_keyword_deletion()
    {
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = Request::create(
            "/event/{$cdbid}/keywords",
            Request::METHOD_DELETE
        );
        $request->query->set('keyword', 'foo');

        $response = $this->controller->deleteKeyword($request, $cdbid);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $link = $this->entryapiLinkBaseUrl . $cdbid;
        $rsp = new Rsp('0.1', 'INFO', 'KeywordWithdrawn', $link, null);

        $this->assertEquals($rsp->toXml(), $response->getContent());
    }

    /**
     * @test
     * @dataProvider exceptionResponseProvider
     *
     * @param \Exception $exception
     * @param Rsp $expectedResponse
     */
    public function it_can_convert_exceptions_into_responses(
        \Exception $exception,
        Rsp $expectedResponse
    ) {
        // Technically it's not the EventRepository that throws these
        // exceptions, but it's the easiest to mock and the code doesn't care
        // where the exception was thrown exactly.
        $this->eventRepository->expects($this->once())
            ->method('save')
            ->willThrowException($exception);

        // Mocking any supported request will cause the controller to check
        // for any exceptions. Deleting a keyword is easiest at the moment.
        $cdbid = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $request = Request::create(
            "/event/{$cdbid}/keywords",
            Request::METHOD_DELETE
        );
        $request->query->set('keyword', 'foo');
        $response = $this->controller->deleteKeyword($request, $cdbid);

        // Make sure we get the expected response for the given exception.
        $this->assertEquals($expectedResponse->toXml(), $response->getContent());
    }

    /**
     * Data provider of responses for specific exceptions.
     * @return array
     */
    public function exceptionResponseProvider()
    {
        $genericMessage = 'An error occurred.';

        return [
            [
                new TooLargeException($genericMessage),
                rsp::error(
                    'FileSizeTooLarge',
                    $genericMessage
                )
            ],
            [
                new XMLSyntaxException($genericMessage),
                rsp::error(
                    'XmlSyntaxError',
                    $genericMessage
                )
            ],
            [
                new ElementNotFoundException('mockExpectedElement', 'mockFoundElement'),
                rsp::error(
                    'ElementNotFoundError',
                    'Expected mockExpectedElement, found mockFoundElement'
                )
            ],
            [
                new UnexpectedNamespaceException('mock:invalid:ns', array('mock:valid:ns')),
                rsp::error(
                    'XmlSyntaxError',
                    'Unexpected namespace "mock:invalid:ns", expected one of: mock:valid:ns'
                )
            ],
            [
                new UnexpectedRootElementException('mock-invalid-rootElement', 'mock-valid-rootElement'),
                rsp::error(
                    'XmlSyntaxError',
                    'Unexpected root element "mock-invalid-rootElement", expected mock-valid-rootElement'
                )
            ],
            [
                new SchemaValidationException('mock:ns'),
                rsp::error(
                    'XmlSyntaxError',
                    'The XML document does not validate with mock:ns'
                )
            ],
            [
                new TooManyItemsException(),
                rsp::error(
                    'TooManyItems',
                    'Too many items in your messages.'
                )
            ],
            [
                new SuspiciousContentException(),
                rsp::error(
                    'SuspectedContent',
                    'Suspicious content found. Account deactivated.'
                )
            ],
            [
                new EventNotFoundException($genericMessage),
                rsp::error(
                    'NotFound',
                    'Resource not found'
                )
            ],
            [
                new \InvalidArgumentException($genericMessage),
                rsp::error(
                    'UnexpectedFailure',
                    $genericMessage
                )
            ],
            [
                new \Exception($genericMessage),
                rsp::error(
                    'UnexpectedFailure',
                    $genericMessage
                )
            ],
        ];
    }
}
