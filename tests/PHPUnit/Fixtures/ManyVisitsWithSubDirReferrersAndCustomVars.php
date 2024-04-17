<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks 13 visits all with custom variables and referrer URLs
 * w/ sub-dirs (ie, /path/to/page/has/many/dirs.htm).
 */
class ManyVisitsWithSubDirReferrersAndCustomVars extends Fixture
{
    public $dateTime = '2010-03-05 11:22:33';
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

        for ($referrerSite = 1; $referrerSite < 4; $referrerSite++) {
            for ($referrerPage = 1; $referrerPage < 3; $referrerPage++) {
                $offset = $referrerSite * 3 + $referrerPage;
                $t = self::getTracker($idSite, Date::factory($dateTime)->addHour($offset)->getDatetime());
                $t->setUrlReferrer('http://www.referrer' . $referrerSite . '.com/sub/dir/page' . $referrerPage . '.html');
                $t->setCustomVariable(1, 'CustomVarVisit', 'CustomVarValue' . $referrerPage, 'visit');
                for ($page = 0; $page < 3; $page++) {
                    $t->setUrl('http://example.org/dir' . $referrerSite . '/sub/dir/page' . $page . '.html');
                    $t->setCustomVariable(1, 'CustomVarPage', 'CustomVarValue' . $page, 'page');
                    self::checkResponse($t->doTrackPageView('title'));
                }
            }
        }

        $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(24)->getDatetime());
        $t->setCustomVariable(1, 'CustomVarVisit', 'CustomVarValue1', 'visit');
        $t->setUrl('http://example.org/sub/dir/dir1/page1.html');
        $t->setCustomVariable(1, 'CustomVarPage', 'CustomVarValue1', 'page');
        self::checkResponse($t->doTrackPageView('title'));

        $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(24)->getDatetime());
        $t->setUrl('http://example.org/page1.html');
        self::checkResponse($t->doTrackPageView('title'));
    }
}
