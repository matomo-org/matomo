<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Fixtures;

use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache;

class RandomizedConfigIdVisitsFixture extends Fixture
{
    public $dateTime = '2015-01-11 01:00:00';
    public $idSite = 1;
    public $dropDatabaseInTearDown = false; // temporary to be able to debug db

    public function setUp(): void
    {
        Option::set(PrivacyManager::OPTION_USERID_SALT, 'simpleuseridsalt1');
        Cache::clearCacheGeneral();

        $this->setUpWebsite();

        // track visits
        $this->addHour();
        $this->trackStandardVisits(2);

        // track visits with multiple actions
        $this->addHour();
        $this->trackVisitsWithMultipleActions(3, 2);

        // track visits with set UserID
        $this->addHour();
        $this->trackVisitsWithUserId(2);

        // track ecommerce order
        $this->addHour();
        $this->trackEcommerceOrder(3);

        $this->addDay();

        // track visits WITH config id randomisation
        $this->addHour();
        $this->trackStandardVisits(2, true);

        // track visits with multiple actions WITH config id randomisation
        $this->addHour();
        $this->trackVisitsWithMultipleActions(3, 2, true);

        // track visits with set UserID WITH config id randomisation
        $this->addHour();
        $this->trackVisitsWithUserId(2, true);

        // track ecommerce order WITH config id randomisation
        $this->addHour();
        $this->trackEcommerceOrder(3, true);
    }

    public function tearDown(): void
    {
        // empty
    }

    private function getPrivacyConfig()
    {
        return new Config();
    }

    private function addHour()
    {
        $this->dateTime = Date::factory($this->dateTime)->addPeriod(1, 'hour')->getDatetime();
    }

    private function addDay()
    {
        $this->dateTime = Date::factory($this->dateTime)->addDay(1)->getDatetime();
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    protected function trackStandardVisits(int $visits, bool $randomizeConfigId = false)
    {
        $this->getPrivacyConfig()->randomizeConfigId = $randomizeConfigId;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://example.com/');
        for ($v = 1; $v <= $visits; $v++) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime());
            self::checkResponse($t->doTrackPageView("Standard visit - $v"));
        }
    }

    protected function trackVisitsWithMultipleActions(int $visits, int $actions, bool $randomizeConfigId = false)
    {
        $this->getPrivacyConfig()->randomizeConfigId = $randomizeConfigId;

        for ($v = 1; $v <= $visits; $v++) {
            $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
            $t->setUrl('http://example.com/');
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime());

            self::checkResponse($t->doTrackPageView("Visit with actions - $v"));
            for ($a = 1; $a <= $actions; $a++) {
                $t->setForceVisitDateTime(
                    Date::factory($this->dateTime)
                        ->addPeriod($v, 'minute')
                        ->addPeriod($a, 'second')
                        ->getDatetime()
                );
                self::checkResponse($t->doTrackAction("http://example.com/$v-$a", 'link'));
            }
        }
    }

    protected function trackVisitsWithUserId(int $visits, bool $randomizeConfigId = false)
    {
        $this->getPrivacyConfig()->randomizeConfigId = $randomizeConfigId;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUserId('foobar');
        $t->setUrl('http://example.com/');
        for ($v = 1; $v <= $visits; $v++) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime());
            self::checkResponse($t->doTrackPageView("Visit with user ID set - $v"));
        }
    }

    protected function trackEcommerceOrder(int $orders, bool $randomizeConfigId = false)
    {
        $this->getPrivacyConfig()->randomizeConfigId = $randomizeConfigId;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://example.com/myorder');
        self::checkResponse($t->doTrackPageView('Visit with ecommerce order'));

        for ($o = 1; $o <= $orders; $o++) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($o, 'second')->getDatetime());
            $t->doTrackEcommerceOrder("Ecommerce order ID - $o", 10 * $o, 7, 2, 1, 0);
        }
    }
}
