<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 02/10/15
 * Time: 14:53
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\XmlString;

/**
 * Provides a command to add an event from CdbXml.
 */
class AddEventFromCdbXml
{
    /**
     * @var XmlString
     */
    protected $xml;

    /**
     * @param XmlString $xml
     */
    public function __construct(XmlString $xml)
    {
        $this->xml = $xml;
    }

    /**
     * @return XmlString
     */
    public function getXml()
    {
        return $this->xml;
    }
}
