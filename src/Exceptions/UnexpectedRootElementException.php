<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 11:47
 */

namespace CultuurNet\UDB3SilexEntryAPI\Exceptions;

class UnexpectedRootElementException extends InvalidCdbXmlException
{
    public function __construct($localName, $expectedLocalName)
    {
        parent::__construct(
            'Unexpected root element ' . $localName . ', expected ' . $expectedLocalName
        );
    }
}
