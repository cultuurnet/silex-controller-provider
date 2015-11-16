<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 16/11/15
 * Time: 13:56
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class DeleteTranslationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'someid';
        $expectedLanguage = 'fr';

        $applyTranslation = new DeleteTranslation(
            new String('someid'),
            new Language('fr')
        );

        $this->assertEquals(
            $expectedId,
            $applyTranslation->getEventId()
        );

        $this->assertEquals(
            $expectedLanguage,
            $applyTranslation->getLanguage()
        );
    }
}
