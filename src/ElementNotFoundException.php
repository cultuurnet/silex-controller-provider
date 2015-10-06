<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 13:17
 */

namespace CultuurNet\UDB3SilexEntryAPI;

class ElementNotFoundException extends InvalidCdbXmlException
{
    public function __construct($expectedElement, $elementFound = null)
    {
        $errorMessage = 'Expected ' . $expectedElement;

        if ($elementFound != null) {
            $errorMessage .= ', found ' . $elementFound;
        }

        parent::__construct($errorMessage);
    }
}
