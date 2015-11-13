<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 27/10/15
 * Time: 10:46
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use ValueObjects\String\String;

class MergeLabels
{
    /**
     * @var String|String
     */
    protected $eventId;

    /**
     * @var LabelCollection
     */
    protected $labels;

    /**
     * @param String $eventId
     * @param LabelCollection $labels
     */
    public function __construct(String $eventId, LabelCollection $labels)
    {
        $this->eventId = $eventId;
        $this->labels = $labels;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return LabelCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
