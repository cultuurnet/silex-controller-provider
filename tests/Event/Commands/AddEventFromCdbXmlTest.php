<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 28/10/15
 * Time: 17:04
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3SilexEntryAPI\SizeLimitedEventXmlString;
use ValueObjects\String\String;

class AddEventFromCdbXmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'someid';
        $xml = new SizeLimitedEventXmlString(file_get_contents(__DIR__ . '/samples/Valid.xml'));
        $expectedXmlString = $xml;
        $addEventFromCdbXml = new AddEventFromCdbXml(
            new String('someid'),
            $xml
        );

        $this->assertEquals(
            $expectedId,
            $addEventFromCdbXml->getEventId()
        );

        $this->assertEquals(
            $expectedXmlString,
            $addEventFromCdbXml->getXml()
        );
    }
}
