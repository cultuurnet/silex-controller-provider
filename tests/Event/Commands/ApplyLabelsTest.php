<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 28/10/15
 * Time: 16:57
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3SilexEntryAPI\KeywordsVisiblesPair;
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
        $expectedKeywordsString = new KeywordsVisiblesPair($keywordsString);

        $applyLabels = new MergeLabels(
            new String('someid'),
            new KeywordsVisiblesPair($keywordsString)
        );

        $this->assertEquals(
            $expectedId,
            $applyLabels->getEventId()
        );

        $this->assertEquals(
            $expectedKeywordsString,
            $applyLabels->getLabels()
        );
    }
}
