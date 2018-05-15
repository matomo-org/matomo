<?php

namespace Piwik\Tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

class OneVisitWithSiteSearch extends Fixture
{
    public $dateTime = '2012-01-11 07:22:33';
    public $idSite = 1;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        $tracker = self::getTracker($this->idSite, $this->dateTime, true);

        $tracker->setTokenAuth(self::getTokenAuth());
        $tracker->setUrl('http://example.org/sites/search.html');
        $tracker->setForceVisitDateTime($this->dateTime);

        self::checkResponse(
            $tracker->doTrackSiteSearch(
                'title%3A"test"+AND+body%3A"test"+AND+spaceCategory%3AALL+AND+startIndex%3A0+AND+pageSize%3A10',
                '',
                3
            )
        );
    }
}
