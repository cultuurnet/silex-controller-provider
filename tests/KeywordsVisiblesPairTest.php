<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 09:41
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use ValueObjects\String\String;

class KeywordsVisiblesPairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_refuses_too_many_visibles_values()
    {
        $this->setExpectedException(\CultuurNet\UDB3\UnequalAmountOfValuesException::class);
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
        $this->setExpectedException(\CultuurNet\UDB3\UnequalAmountOfValuesException::class);
        new KeywordsVisiblesPair(
            new String('keyword a;keyword b;keyword c'),
            new String('false, true')
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
}
