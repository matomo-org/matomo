<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks one visit w/ pageview URLs that are not normalized.
 * These URLs use different protocols and a mix of lowercase & uppercase letters.
 */
class OneVisitWithAbnormalPageviewUrls extends Fixture
{
    public $dateTime = '2010-03-06 11:22:33';
    public $idSite = 1;

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
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        $t->setUrlReferrer('http://www.google.com/search?q=piwik');
        $t->setUrl('http://example.org/foo/bar.html');
        self::checkResponse($t->doTrackPageView('http://incredible.title/'));

        $t->setUrl('https://example.org/foo/bar.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.2)->getDatetime());
        self::checkResponse($t->doTrackPageView('https://incredible.title/'));

        $t->setUrl('https://wWw.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackPageView('http://www.incredible.title/'));

        $t->setUrl('http://WwW.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackPageView('https://www.incredible.title/'));

        $t->setUrl('http://www.example.org/foo/bar3.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.5)->getDatetime());
        self::checkResponse($t->doTrackPageView('incredible.title/'));

        $t->setUrl('http://www.my.url/ꟽ碌㒧䊶亄ﶆⅅขκもኸόσशμεޖृ');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.7)->getDatetime());
        self::checkResponse($t->doTrackPageView('Valid URL, although strange looking'));

        $t->setUrl('https://make.wordpress.org/?emoji=😎l&param=test');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.8)->getDatetime());
        self::checkResponse($t->doTrackPageView('Emoji here: %F0%9F%98%8E'));

        // this pageview should be last
        $t->setUrl('https://example.org/foo/bar4.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.6)->getDatetime());
        self::checkResponse($t->doTrackPageView('incredible.title/'));
    }
}
