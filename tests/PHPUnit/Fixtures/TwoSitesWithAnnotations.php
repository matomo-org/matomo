<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Annotations\API;
use Piwik\Tests\Framework\Fixture;

/**
 * A fixture that adds two websites and annotations for each website.
 */
class TwoSitesWithAnnotations extends Fixture
{
    public $dateTime = '2011-01-01 00:11:42';
    public $idSite1 = 1;
    public $idSite2 = 2;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->addAnnotations();
        $this->addSpecialCharsAnnotations();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function addAnnotations()
    {
        // add two annotations per week for three months, starring every third annotation
        // first month in 2011, second two in 2012
        $count = 0;
        $dateStart = Date::factory('2011-12-01');
        $dateEnd = Date::factory('2012-03-01');
        while ($dateStart->getTimestamp() < $dateEnd->getTimestamp()) {
            $starred = $count % 3 == 0 ? 1 : 0;
            $site1Text = "$count: Site 1 annotation for " . $dateStart->toString();
            $site2Text = "$count: Site 2 annotation for " . $dateStart->toString();

            API::getInstance()->add($this->idSite1, $dateStart->toString(), $site1Text, $starred);
            API::getInstance()->add($this->idSite2, $dateStart->toString(), $site2Text, $starred);

            $nextDay = $dateStart->addDay(1);
            ++$count;

            $starred = $count % 3 == 0 ? 1 : 0;
            $site1Text = "$count: Site 1 annotation for " . $nextDay->toString();
            $site2Text = "$count: Site 2 annotation for " . $nextDay->toString();

            API::getInstance()->add($this->idSite1, $nextDay->toString(), $site1Text, $starred);
            API::getInstance()->add($this->idSite2, $nextDay->toString(), $site2Text, $starred);

            $dateStart = $dateStart->addPeriod(1, 'WEEK');
            ++$count;
        }
    }

    private function addSpecialCharsAnnotations()
    {
        $date = '2012-04-01';
        $t = self::getTracker($this->idSite1, $date, $defaultInit = true);
        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));

        $api = API::getInstance();
        $api->add($this->idSite1, $date, '<h1>Test</h1>');
        $api->add($this->idSite1, $date, '<script>alert(1)</script>');
        $api->add($this->idSite1, $date, '{{ 1+1 }}');
        $api->add($this->idSite1, $date, '<div vue-entry="Dashboard.DashboardSettings"></div>');
        $api->add($this->idSite1, $date, "1' or '1' = '1'))/*");
    }

    private function setUpWebsitesAndGoals()
    {
        // add two websites
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime, $ecommerce = 1);
        }

        if (!self::siteCreated($idSite = 2)) {
            self::createWebsite($this->dateTime, $ecommerce = 1);
        }
    }
}
