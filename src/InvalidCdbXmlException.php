<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 11:01
 */

namespace CultuurNet\UDB3SilexEntryAPI;

class InvalidCdbXmlException extends \InvalidArgumentException
{
    public static function unexpectedNamespace($namespace, $validNamespaces)
    {
        return new InvalidCdbXmlException(
            'Unexpected namespace' . $namespace . ', expected one of: ' . implode(', ', $validNamespaces)
        );
    }
}
