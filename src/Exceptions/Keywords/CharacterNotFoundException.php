<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 26/10/15
 * Time: 16:30
 */

namespace CultuurNet\UDB3SilexEntryAPI\Exceptions\Keywords;

class CharacterNotFoundException extends InvalidKeywordsStringException
{
    public function __construct($expectedCharacter)
    {
        $errorMessage = 'Expected ' . $expectedCharacter;

        parent::__construct($errorMessage);
    }
}
