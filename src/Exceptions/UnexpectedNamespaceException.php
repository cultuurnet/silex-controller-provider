<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 11:44
 */

namespace CultuurNet\UDB3SilexEntryAPI\Exceptions;

class UnexpectedNamespaceException extends InvalidCdbXmlException
{
    public function __construct($namespace, $validNamespaces)
    {
        parent::__construct(
            'Unexpected namespace "' . $namespace . '", expected one of: ' . implode(', ', $validNamespaces)
        );
    }
}
