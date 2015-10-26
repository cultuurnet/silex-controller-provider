<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 26/10/15
 * Time: 12:52
 */

namespace CultuurNet\UDB3SilexEntryAPI\Exceptions;

use Exception;

class EventUpdatedException extends Exception
{
    public function __construct($cdbid)
    {
        parent::__construct($cdbid);
    }
}
