<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 21/10/15
 * Time: 12:45
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\EventXmlString;
use ValueObjects\String\String;

class UpdateEventFromCdbXml
{
    /**
     * @var EventXmlString
     */
    protected $xml;

    /**
     * @var String|String
     */
    protected $eventId;

    /**
     * @param String $eventId
     * @param EventXmlString $xml
     */
    public function __construct(String $eventId, EventXmlString $xml)
    {
        $this->eventId = $eventId;
        $this->xml = $xml;
    }

    /**
     * @return EventXmlString
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
