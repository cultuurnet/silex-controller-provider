<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 14:43
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use CultuurNet\UDB3\XmlString;

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
