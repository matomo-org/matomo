<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Fixture;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

class TrackingFailures extends Fixture
{
    public $idSite = 1;
    public $dateTime = '2013-01-02 03:04:05';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        if (!self::siteCreated($this->idSite)) {
            Fixture::createWebsite('2014-01-02 03:04:05');
        }
        $this->trackData();
    }

    private function trackData()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        self::checkResponse($t->doTrackPageView('Valid Site'));

        $t = self::getTracker(99999, Date::now()->getDatetime(), $defaultInit = true);

        for ($i = 0; $i < 2; $i++) {
            // we trigger it multiple times to test it will be inserted only once
            $t->doTrackPageView('Invalid Site');
        }

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('10.11.12.13');
        $t->setTokenAuth('foobar'); //  wrong token
        $t->doTrackPageView('Invalid Token');
    }
}
