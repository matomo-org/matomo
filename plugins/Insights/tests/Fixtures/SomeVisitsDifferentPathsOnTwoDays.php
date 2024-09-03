<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one website and tracks several visits from one visitor on
 * different days that span about a month apart.
 */
class SomeVisitsDifferentPathsOnTwoDays extends Fixture
{
    public $idSite = 1;
    public $date1  = '2010-12-14';
    public $date2  = '2010-12-13';

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            $this->idSite = self::createWebsite('2008-12-12 00:00:00', $ecommerce = 0, $siteName = 'Site AAAAAA');
        }
    }

    private function trackVisits()
    {
        $this->trackPageViews($this->date2, array(
            '/Mover1' => 2,
            '/Old1' => 9,
            '/Mover2' => 24,
            '/Mover3' => 21,
            '/Old2' => 3
        ));

        $this->trackPageViews($this->date1, array(
            '/Mover1' => 10,
            '/New1' => 5,
            '/Mover2' => 13,
            '/Mover3' => 20,
            '/New2' => 2
        ));
    }

    private function trackPageViews($date, $paths)
    {
        $date = Date::factory($date . ' 00:02:00');
        $numPageViews = 1;

        foreach ($paths as $path => $numVisits) {
            for ($index = 0; $index < $numVisits; $index++) {
                $tracker = self::getTracker($this->idSite, $date->getDatetime(), $defaultInit = true);
                $date    = $date->addHour(.1);
                $tracker->setUrl('http://example.org' . $path);
                $tracker->setIp('156.15.13.' . $numPageViews);
                $tracker->setResolution(1000, 1000 + $numPageViews);

                $response = $tracker->doTrackPageView('Hello');
                self::checkResponse($response);

                $numPageViews++;
            }
        }
    }
}
