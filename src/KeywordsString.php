<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 26/10/15
 * Time: 16:17
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use CultuurNet\UDB3SilexEntryAPI\Exceptions\Keywords\CharacterNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\Keywords\KeyNotFoundException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\Keywords\TooManySpecificCharactersException;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\Keywords\UnequalAmountOfValuesException;
use ValueObjects\String\String;

class KeywordsString extends String
{
    /**
     * @var array
     */
    protected $keywords;

    /**
     * @var array
     */
    protected $visibles;

    /**
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @return array
     */
    public function getVisibles()
    {
        return $this->visibles;
    }

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $expectedKeys = array('keywords', 'visibles');
        $valuesDelimiter = ';';
        $keysDelimiter = '&';

        if ((strpos($value, $keysDelimiter) === false)) {
            throw new CharacterNotFoundException($keysDelimiter);
        } elseif (substr_count($value, '&') > 1) {
            throw new TooManySpecificCharactersException($keysDelimiter);
        }

        $data = explode($keysDelimiter, $value);

        foreach ($expectedKeys as $key => $expectedKey) {
            $this->checkForKey($data[$key], $expectedKey);
            $this->{$expectedKey} = $this->parseValues($data[$key], $valuesDelimiter);
        }

        if (count($this->keywords) != count($this->visibles)) {
            throw new UnequalAmountOfValuesException('keywords', 'visibles');
        }

        parent::__construct($value);
    }

    /**
     * Helper function to check if a certain key exists.
     * @param string $value
     * @param string $key
     */
    public function checkForKey($value, $key)
    {
        $value = $value . '=';
        if (strpos($value, $key) === false) {
            throw new KeyNotFoundException($key);
        }
    }

    /**
     * @param string $keyWithValues
     * @param string $valuesDelimiter
     * @return array
     */
    public function parseValues($keyWithValues, $valuesDelimiter)
    {
        $keyAndValuesParts = explode('=', $keyWithValues);
        $valuesPart = $keyAndValuesParts[1];

        if (strpos($valuesPart, $valuesDelimiter) === false) {
            $values = array($valuesPart);
        } else {
            $values = explode($valuesDelimiter, $valuesPart);
        }

        return $values;
    }
}
