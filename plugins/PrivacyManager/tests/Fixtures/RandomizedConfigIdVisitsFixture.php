<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Fixtures;

use Piwik\Config;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\FeatureFlags\Storage\ConfigFeatureFlagStorage;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\FeatureFlags\ConfigIdRandomisation;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache;

class RandomizedConfigIdVisitsFixture extends Fixture
{
    public $dateTime = '2015-01-01 01:00:00';
    public $idSite = 1;
    public $dropDatabaseInTearDown = false; // temporary to be able to debug db

    /** @var ConfigFeatureFlagStorage */
    private $configFeatureFlagStorage;

    /** @var PrivacyManagerConfig */
    private $privacyManagerConfig;

    public function setUp(): void
    {
        Option::set(PrivacyManager::OPTION_USERID_SALT, 'simpleuseridsalt1');
        Cache::clearCacheGeneral();

        $config =
        $this->configFeatureFlagStorage = new ConfigFeatureFlagStorage(Config::getInstance());
        $this->privacyManagerConfig = new PrivacyManagerConfig();

        $this->setUpWebsite();

        // config off, feature flag off
        // should NOT randomise
        $this->trackVisits(false, false);
        $this->addMonth();

        // config on, feature flag on
        // should randomise
        $this->trackVisits(true, true);
        $this->addMonth();

        // config on, feature flag off
        // should NOT randomise
        $this->trackVisits(true, false);
        $this->addMonth();

        // config off, feature flag on
        // should NOT randomise
        $this->trackVisits(false, true);
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setConfigIdRandomisationPrivacyConfig(bool $config)
    {
        $this->privacyManagerConfig->randomizeConfigId = $config;
    }

    private function setConfigIdRandomisationFeatureFlag(bool $status)
    {
        $flag = new ConfigIdRandomisation();

        if ($status === true) {
            $this->configFeatureFlagStorage->enableFeatureFlag($flag);
        } else {
            $this->configFeatureFlagStorage->disableFeatureFlag($flag);
        }
    }

    private function addHour()
    {
        $this->dateTime = Date::factory($this->dateTime)->addPeriod(1, 'hour')->getDatetime();
    }

    private function addMonth()
    {
        $this->dateTime = Date::factory($this->dateTime)->addMonth(1)->getDatetime();
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
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime());
            self::checkResponse($t->doTrackPageView("Standard visit - $v"));
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

    protected function trackVisitsWithUserId(int $visits)
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUserId('foobar');
        $t->setUrl('http://example.com/');
        for ($v = 1; $v <= $visits; $v++) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($v, 'minute')->getDatetime());
            self::checkResponse($t->doTrackPageView("Visit with user ID set - $v"));
        }
    }

    protected function trackEcommerceOrder(int $orders)
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://example.com/myorder');
        self::checkResponse($t->doTrackPageView('Visit with ecommerce order'));

        for ($o = 1; $o <= $orders; $o++) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addPeriod($o, 'second')->getDatetime());
            $t->doTrackEcommerceOrder('Ecommerce order ID - ' . rand(1, 1000000), 10 * rand(1, 100), 7, 2, 1, 0);
        }
    }

    /**
     * Track a set of visits for the test to then evaluate in the database
     *
     * @param bool $randomizeConfigId
     * @param bool $configRandomisationFeatureFlag
     * @return void
     */
    protected function trackVisits(bool $randomizeConfigId, bool $configRandomisationFeatureFlag)
    {
        $this->setConfigIdRandomisationPrivacyConfig($randomizeConfigId);
        $this->setConfigIdRandomisationFeatureFlag($configRandomisationFeatureFlag);

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
