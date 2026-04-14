<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

class MultiSiteResolutionReport extends Fixture
{
    public $dateTime = '2009-01-04 00:11:42';
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

        $siteOneTracker = self::getTracker($this->idSite, $this->dateTime, true);
        $siteOneTracker->setResolution(100, 100);
        $siteOneTracker->setUrl('http://example.org/index.htm');
        self::checkResponse($siteOneTracker->doTrackPageView('site one'));

        $siteTwoTracker = self::getTracker($this->idSite2, $this->dateTime, true);
        $siteTwoTracker->setResolution(200, 200);
        $siteTwoTracker->setUrl('http://example-two.org/index.htm');
        self::checkResponse($siteTwoTracker->doTrackPageView('site two'));
    }

    public function tearDown(): void
    {
    }
}
