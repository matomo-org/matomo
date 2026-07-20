<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

class TwoSitesWithResolutions extends Fixture
{
    public $dateTime = '2015-03-04 03:24:00';
    public $idSite = 1;
    public $idSite2 = 2;

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::siteCreated($this->idSite2)) {
            self::createWebsite($this->dateTime);
        }

        $tracker = self::getTracker($this->idSite, $this->dateTime, true);
        $tracker->setUrl('http://example.org/site-1');
        $tracker->setResolution(800, 600);
        self::checkResponse($tracker->doTrackPageView('site 1 resolution'));

        $tracker = self::getTracker($this->idSite2, $this->dateTime, true);
        $tracker->setUrl('http://example.org/site-2');
        $tracker->setResolution(1024, 768);
        self::checkResponse($tracker->doTrackPageView('site 2 resolution'));
    }

    public function tearDown(): void
    {
        // empty
    }
}
