<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 09:41
 */

namespace CultuurNet\UDB3SilexEntryAPI;

class KeywordsStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_validates_for_an_ampersand()
    {
        $this->setExpectedException(Exceptions\Keywords\CharacterNotFoundException::class);
        $keywordsString = new KeywordsString(file_get_contents(__DIR__.'/samples/KeywordsStringWithoutAmpersand.txt'));
    }

    /**
     * @test
     */
    public function it_validates_for_not_more_than_one_ampersand()
    {
        $this->setExpectedException(Exceptions\Keywords\TooManySpecificCharactersException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithTooManyAmpersands.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_for_a_keywords_key()
    {
        $this->setExpectedException(Exceptions\Keywords\KeyNotFoundException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithoutKeywordsKey.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_for_a_visibles_key()
    {
        $this->setExpectedException(Exceptions\Keywords\KeyNotFoundException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithoutVisiblesKey.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_when_using_too_much_visibles_values()
    {
        $this->setExpectedException(Exceptions\Keywords\UnequalAmountOfValuesException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithTooMuchVisiblesValues.txt')
        );
    }

    /**
     * @test
     */
    public function it_validates_when_using_too_much_keywords_values()
    {
        $this->setExpectedException(Exceptions\Keywords\UnequalAmountOfValuesException::class);
        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__.'/samples/KeywordsStringWithTooMuchKeywordsValues.txt')
        );
    }
}
