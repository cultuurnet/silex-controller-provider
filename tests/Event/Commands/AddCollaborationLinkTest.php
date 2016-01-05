<?php

namespace CultuurNet\UDB3SilexEntryAPI\Event\Commands;

use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class AddCollaborationLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $id = new String('someid');
        $language = new Language('fr');
        $collaborationData = new CollaborationData(
            new String('2b88e17a-27fc-4310-9556-4df7188a051f'),
            new String('some plain text')
        );

        $addLink = new AddCollaborationLink(
            $id,
            $language,
            $collaborationData
        );

        $this->assertEquals(
            $id,
            $addLink->getEventId()
        );

        $this->assertEquals(
            $language,
            $addLink->getLanguage()
        );

        $this->assertEquals(
            $collaborationData,
            $addLink->getCollaborationData()
        );
    }
}
