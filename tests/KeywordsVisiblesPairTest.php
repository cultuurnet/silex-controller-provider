<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 09:41
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use ValueObjects\String\String;

class KeywordsVisiblesPairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_refuses_too_many_visibles_values()
    {
        $this->setExpectedException(
            UnequalAmountOfValuesException::class
        );
        new KeywordsVisiblesPair(
            new String('keyword a;keyword b'),
            new String('false;true;false')
        );
    }

    /**
     * @test
     */
    public function it_refuses_too_many_keywords_values()
    {
        $this->setExpectedException(
            UnequalAmountOfValuesException::class
        );
        new KeywordsVisiblesPair(
            new String('keyword a;keyword b;keyword c'),
            new String('false;true')
        );
    }

    /**
     * @test
     */
    public function it_validates_for_a_one_element_values_array()
    {
        $pair = new KeywordsVisiblesPair(
            new String('keyword1'),
            new String('true')
        );

        $expectedKeywords = array('keyword1');
        $expectedVisibles = array('true');

        $this->assertEquals($expectedKeywords, $pair->getKeywords());
        $this->assertEquals($expectedVisibles, $pair->getVisibles());
    }

    /**
     * @test
     */
    public function it_defaults_to_visible_if_visibles_is_empty()
    {
        $pair = new KeywordsVisiblesPair(
            new String('keyword a;keyword b'),
            new String('')
        );

        $this->assertEquals(
            ['keyword a', 'keyword b'],
            $pair->getKeywords()
        );

        $this->assertEquals(
            ['true', 'true'],
            $pair->getVisibles()
        );
    }

    /**
     * @test
     */
    public function it_can_factor_a_label_collection()
    {
        $pair = new KeywordsVisiblesPair(
            new String('keyword a;keyword b;keyword c'),
            new String('false;true;true')
        );

        $expectedLabels = new LabelCollection(
            [
                new Label('keyword a', false),
                new Label('keyword b', true),
                new Label('keyword c', true),
            ]
        );

        $labels = $pair->getLabels();

        $this->assertEquals(
            $expectedLabels,
            $labels
        );
    }
}
