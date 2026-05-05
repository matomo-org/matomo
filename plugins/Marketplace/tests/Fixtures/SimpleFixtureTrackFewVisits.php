<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Fixtures\UITestFixture;

class SimpleFixtureTrackFewVisits extends UITestFixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();
        $this->trackVisits();
    }

    protected function trackVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.73');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/sub/page');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));
    }
}
