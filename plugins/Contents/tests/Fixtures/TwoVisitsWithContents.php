<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Contents\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Tests\Framework\Fixture;
use MatomoTracker;

/**
 * Tracks contents
 */
class TwoVisitsWithContents extends Fixture
{
    public $dateTime = '2010-01-03 11:22:33';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    private function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            // These two goals are to check contents don't trigger for URL or Title matching
            APIGoals::getInstance()->addGoal($this->idSite, 'triggered js', 'url', 'webradio', 'contains');
            APIGoals::getInstance()->addGoal($this->idSite, 'triggered js', 'title', 'Music', 'contains');
        }
    }

    public function trackVisits()
    {
        $uselocal = false;
        $vis = self::getTracker($this->idSite, $this->dateTime, $useDefault = true, $uselocal);

        $this->trackContentImpressionsAndInteractions($vis);

        $this->dateTime = Date::factory($this->dateTime)->addHour(0.5)->getDatetime();
        $vis2 = self::getTracker($this->idSite, $this->dateTime, $useDefault = true, $uselocal);
        $vis2->setIp('111.1.1.1');
        $vis2->setPlugins($flash = false, $java = false);

        $this->trackContentImpressionsAndInteractions($vis2);
    }

    private function moveTimeForward(MatomoTracker $vis, $minutes)
    {
        $hour = $minutes / 60;
        $vis->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($hour)->getDatetime());
    }

    protected function trackContentImpressionsAndInteractions(MatomoTracker $vis)
    {
        $vis->setUrl('http://www.example.org/page');
        $vis->setPerformanceTimings(33, 325, 124, 356, 215, 99);
        self::checkResponse($vis->doTrackPageView('Ads'));

        self::checkResponse($vis->doTrackContentImpression('ImageAd'));
        self::checkResponse($vis->doTrackContentImpression('ImageAd', ''));

        $this->moveTimeForward($vis, 2);
        self::checkResponse($vis->doTrackContentImpression('ImageAd', '/path/ad.jpg', 'http://www.example.com'));
        self::checkResponse($vis->doTrackContentImpression('ImageAd', '/path/ad2.jpg', 'http://www.example.com'));
        self::checkResponse($vis->doTrackContentInteraction('submit', 'ImageAd', '/path/ad.jpg', 'http://www.example.com'));
        $this->moveTimeForward($vis, 3);
        self::checkResponse($vis->doTrackContentImpression('Text Ad', 'Click to download Piwik now', 'http://piwik.org/download'));
        self::checkResponse($vis->doTrackContentImpression('Text Ad', 'Click NOW', 'http://piwik.org/'));
        self::checkResponse($vis->doTrackContentInteraction('click', 'Text Ad', 'Click to download Piwik now', 'http://piwik.org/download'));
        self::checkResponse($vis->doTrackContentInteraction('click', 'Text Ad', 'Click NOW', 'http://piwik.org/download'));
        $this->moveTimeForward($vis, 4);
        self::checkResponse($vis->doTrackContentImpression('Text Ad', 'Click to download Piwik now', ''));

        $this->moveTimeForward($vis, 4.5);
        self::checkResponse($vis->doTrackContentImpression('Video Ad', 'movie.mov'));

        $vis->setUrl('http://www.example.com/de/suche?q=foo');
        $this->moveTimeForward($vis, 4.5);
        self::checkResponse($vis->doTrackContentImpression('Video Ad', 'movie.mov'));
    }

    public function tearDown(): void
    {
    }
}
