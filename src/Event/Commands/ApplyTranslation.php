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
     * @var String|String
     */
    protected $title;

    /**
     * @var String|String
     */
    protected $shortDescription;

    /**
     * @var String|String
     */
    protected $longDescription;

    /**
     * ApplyTranslation constructor.
     * @param String $eventId
     * @param Language $language
     * @param String|null $title
     * @param String|null $shortDescription
     * @param String|null $longDescription
     */
    public function __construct(
        String $eventId,
        Language $language,
        String $title = null,
        String $shortDescription = null,
        String $longDescription = null
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
     * @return String|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return String|null
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return String|null
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }
}
