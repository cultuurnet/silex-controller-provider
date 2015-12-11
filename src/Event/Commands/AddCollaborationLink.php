<?php

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class AddCollaborationLink
{
    /**
     * @var String|String
     */
    protected $eventId;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var CollaborationData
     */
    protected $collaborationData;

    /**
     * @param String $eventId
     * @param Language $language
     * @param CollaborationData $collaborationData
     */
    public function __construct(
        String $eventId,
        Language $language,
        CollaborationData $collaborationData
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
        $this->collaborationData = $collaborationData;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return CollaborationData
     */
    public function getCollaborationData()
    {
        return $this->collaborationData;
    }
}
