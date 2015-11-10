<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 10/11/15
 * Time: 10:13
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class ApplyTranslationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'someid';
        $expectedLanguage = 'fr';
        $expectedTitle = 'Title';
        $expectedShortDescription = 'Short description';
        $expectedLongDescription = 'Long long long extra long description';

        $applyTranslation = new ApplyTranslation(
            new String('someid'),
            new Language('fr'),
            new String('Title'),
            new String('Short description'),
            new String('Long long long extra long description')
        );

        $this->assertEquals(
            $expectedId,
            $applyTranslation->getEventId()
        );

        $this->assertEquals(
            $expectedLanguage,
            $applyTranslation->getLanguage()
        );

        $this->assertEquals(
            $expectedTitle,
            $applyTranslation->getTitle()
        );

        $this->assertEquals(
            $expectedShortDescription,
            $applyTranslation->getShortDescription()
        );

        $this->assertEquals(
            $expectedLongDescription,
            $applyTranslation->getLongDescription()
        );
    }
}
