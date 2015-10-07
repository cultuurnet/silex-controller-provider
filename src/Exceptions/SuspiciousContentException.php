<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 14:05
 */

namespace CultuurNet\UDB3SilexEntryAPI\Exceptions;

class SuspiciousContentException extends InvalidCdbXmlException
{
    public function __construct()
    {
        parent::__construct(
            'Suspicious content found. Account deactivated.'
        );
    }
}
