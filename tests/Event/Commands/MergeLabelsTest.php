<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 28/10/15
 * Time: 16:57
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use ValueObjects\String\String;

class MergeLabelsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'someid';
        $collection = new LabelCollection(
            [
                new Label('keyword 1', true),
                new Label('keyword 2', true),
            ]
        );

        $applyLabels = new MergeLabels(
            new String('someid'),
            $collection
        );

        $this->assertEquals(
            $expectedId,
            $applyLabels->getEventId()
        );

        $this->assertEquals(
            $collection,
            $applyLabels->getLabels()
        );
    }
}
