<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 04/11/15
 * Time: 17:10
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class ApplyTranslation
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
     * @var String
     */
    protected $title;

    /**
     * @var String
     */
    protected $shortDescription;

    /**
     * @var String
     */
    protected $longDescription;

    /**
     * ApplyTranslation constructor.
     * @param String $eventId
     * @param Language $language
     * @param String $title
     * @param String $shortDescription
     * @param String $longDescription
     */
    public function __construct(
        String $eventId,
        Language $language,
        String $title,
        String $shortDescription,
        String $longDescription
    ) {
        $this->eventId = $eventId;
        $this->language = $language;
        $this->title = $title;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
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
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return String
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return String
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }
}
