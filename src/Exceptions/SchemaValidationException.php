<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 12:03
 */

namespace CultuurNet\UDB3SilexEntryAPI\Exceptions;

class SchemaValidationException extends InvalidCdbXmlException
{
    public function __construct($namespace)
    {
        parent::__construct(
            'The XML document does not validate with ' . $namespace
        );
    }
}
