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
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache;

class RandomizedConfigIdVisitsFixture extends Fixture
{
    public static $dateTimeNormalConfig = '2015-01-01 01:00:00';
    public static $dateTimeRandomizedConfig = '2015-02-01 01:00:00'; // as above + 1 month

    public $dateTime;
    public $idSite = 1;

    /** @var PrivacyManagerConfig */
    private $privacyManagerConfig;

    public function setUp(): void
    {
        $this->dateTime = self::$dateTimeNormalConfig;

        Option::set(PrivacyManager::OPTION_USERID_SALT, 'simpleuseridsalt1');
        Cache::clearCacheGeneral();

        $this->privacyManagerConfig = new PrivacyManagerConfig();

        $this->setUpWebsite();

        // config off
        // should NOT randomize
        $this->trackVisits(false);

        // config on
        // should randomize
        $this->dateTime = self::$dateTimeRandomizedConfig;
        $this->trackVisits(true);
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setConfigIdRandomisationPrivacyConfig(bool $config)
    {
        $this->privacyManagerConfig->randomizeConfigId = $config;
    }

    private function addHour()
    {
        $this->dateTime = Date::factory($this->dateTime)->addPeriod(1, 'hour')->getDatetime();
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    protected function trackStandardVisits(int $visits)
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://example.com/');
        for ($v = 1; $v <= $visits; $v++) {
            $dt = Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime();
            $t->setForceVisitDateTime($dt);
            self::checkResponse($t->doTrackPageView("Standard visit - $dt"));
        }
    }

    protected function trackVisitsWithMultipleActions(int $visits, int $actions)
    {
        for ($v = 1; $v <= $visits; $v++) {
            $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
            $t->setUrl('http://example.com/');
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime());

            self::checkResponse($t->doTrackPageView("Visit with actions - $v"));
            for ($a = 1; $a <= $actions; $a++) {
                $dt = Date::factory($this->dateTime)
                    ->addPeriod($v, 'minute')
                    ->addPeriod($a, 'second')
                    ->getDatetime();
                $t->setForceVisitDateTime($dt);
                self::checkResponse($t->doTrackAction("http://example.com/$dt", 'link'));
            }
        }
    }

    protected function trackVisitsWithUserId(int $visits)
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUserId('foobar');
        $t->setUrl('http://example.com/');
        for ($v = 1; $v <= $visits; $v++) {
            $dt = Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime();
            $t->setForceVisitDateTime($dt);
            self::checkResponse($t->doTrackPageView("Visit with user ID set - $dt"));
        }
    }

    protected function trackEcommerceOrder(int $orders)
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://example.com/myorder');
        self::checkResponse($t->doTrackPageView('Visit with ecommerce order'));

        for ($o = 1; $o <= $orders; $o++) {
            $dt = Date::factory($this->dateTime)->addPeriod($o, 'second')->getDatetime();
            $t->setForceVisitDateTime($dt);
            $t->doTrackEcommerceOrder('Ecommerce order ID - ' . $dt, 10 * $o, 7, 2, 1, 0);
        }
    }

    protected function trackVisits(bool $randomizeConfigId)
    {
        $this->setConfigIdRandomisationPrivacyConfig($randomizeConfigId);

        // track visits
        $this->trackStandardVisits(2);
        $this->addHour();

        // track visits with multiple actions
        $this->trackVisitsWithMultipleActions(3, 2);
        $this->addHour();

        // track visits with set UserID
        $this->trackVisitsWithUserId(2);
        $this->addHour();

        // track ecommerce order
        $this->trackEcommerceOrder(3);
    }
}
