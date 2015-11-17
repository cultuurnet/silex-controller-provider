<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 16/11/15
 * Time: 13:52
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class DeleteTranslation
{
    /**
     * @var String
     */
    protected $eventId;

    /**
     * @var Language
     */
    protected $language;

    /**
     * DeleteTranslation constructor.
     * @param String $eventId
     * @param Language $language
     */
    public function __construct(
        String $eventId,
        Language $language
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
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
}
