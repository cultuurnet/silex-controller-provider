<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 10:46
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\KeywordsString;
use ValueObjects\String\String;

class ApplyLabels
{
    /**
     * @var String|String
     */
    protected $eventId;

    /**
     * @var KeywordsString
     */
    protected $keywordsString;

    /**
     * @param String $eventId
     * @param KeywordsString $keywordsString
     */
    public function __construct(String $eventId, KeywordsString $keywordsString)
    {
        $this->eventId = $eventId;
        $this->keywordsString = $keywordsString;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return KeywordsString
     */
    public function getKeywordsString()
    {
        return $this->keywordsString;
    }
}
