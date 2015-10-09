<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 14:43
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use CultuurNet\UDB3\XmlString;
use CultuurNet\UDB3SilexEntryAPI\Exceptions\TooLargeException;

class SizeLimitedXmlString extends XmlString
{
    public function __construct($value)
    {
        if (strlen($value) > 102400) {
            throw new TooLargeException();
        }
        parent::__construct($value);

    }
}
