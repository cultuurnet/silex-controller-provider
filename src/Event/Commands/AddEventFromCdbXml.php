<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 02/10/15
 * Time: 14:53
 */

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultureFeed_Cdb_Item_Event;

/**
 * Provides a command to add an event from CdbXml.
 */
class AddEventFromCdbXml
{

    public function __construct(CultureFeed_Cdb_Item_Event $cdbEvent)
    {
        $this->cdbEvent = $cdbEvent;
    }
}
