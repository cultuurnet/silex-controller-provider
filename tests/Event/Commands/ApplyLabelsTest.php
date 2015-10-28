<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 28/10/15
 * Time: 16:57
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\KeywordsString;
use ValueObjects\String\String;

class ApplyLabelsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $keywordsString = file_get_contents(__DIR__ . '/samples/KeywordsStringValid.txt');

        $expectedId = 'someid';
        $expectedKeywordsString = new KeywordsString($keywordsString);

        $applyLabels = new ApplyLabels(
            new String('someid'),
            new KeywordsString($keywordsString)
        );

        $this->assertEquals(
            $expectedId,
            $applyLabels->getEventId()
        );

        $this->assertEquals(
            $expectedKeywordsString,
            $applyLabels->getKeywordsString()
        );
    }
}
