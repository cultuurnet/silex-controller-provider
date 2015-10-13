<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 14:43
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooLargeException;

class SizeLimitedEventXmlString extends EventXmlString
{
    public function __construct($value)
    {
        if (strlen($value) > 102400) {
            throw new TooLargeException();
        }
        parent::__construct($value);

    }
}
