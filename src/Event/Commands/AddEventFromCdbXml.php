<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 02/10/15
 * Time: 14:53
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\XmlString;
use ValueObjects\String\String;

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
     * @var String|String
     */
    protected $eventId;

    /**
     * @param String $eventId
     * @param XmlString $xml
     */
    public function __construct(String $eventId, XmlString $xml)
    {
        $this->eventId = $eventId;
        $this->xml = $xml;
    }

    /**
     * @return XmlString
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }
}
