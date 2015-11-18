<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 18/11/15
 * Time: 15:04
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LinkType;
use ValueObjects\String\String;

class AddLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        //Example taken from http://jira.uitdatabank.be:8080/browse/UDB-2266
        $expectedId = 'someid';
        $expectedLanguage = 'fr';
        $expectedLink = 'http://cultuurnet.be';
        $expectedLinkType = 'roadmap';
        $expectedTitle = 'Title';
        $expectedCopyright = 'Jeroom';
        $expectedSubbrand = '2b88e17a-27fc-4310-9556-4df7188a051f';
        $expectedDescription = 'description';

        $addLink = new AddLink(
            new String('someid'),
            new Language('fr'),
            new String('http://cultuurnet.be'),
            new LinkType('roadmap'),
            new String('Title'),
            new String('Jeroom'),
            new String('2b88e17a-27fc-4310-9556-4df7188a051f'),
            new String('description')
        );

        $this->assertEquals(
            $expectedId,
            $addLink->getEventId()
        );

        $this->assertEquals(
            $expectedLanguage,
            $addLink->getLanguage()
        );

        $this->assertEquals(
            $expectedLink,
            $addLink->getLink()
        );

        $this->assertEquals(
            $expectedLinkType,
            $addLink->getLinkType()->toNative()
        );

        $this->assertEquals(
            $expectedTitle,
            $addLink->getTitle()
        );

        $this->assertEquals(
            $expectedCopyright,
            $addLink->getCopyright()
        );

        $this->assertEquals(
            $expectedSubbrand,
            $addLink->getSubbrand()
        );

        $this->assertEquals(
            $expectedDescription,
            $addLink->getDescription()
        );
    }
}
