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
 * Tracks visits where after each page view a non pageview action is performed
 * The initially tracked page url and title should then still be stored as exit page
 */
class ExitPages extends Fixture
{
    public $idSite = 1;
    public $dateTime = '2023-05-06 12:00:00';

    public function setUp(): void
    {
        Fixture::createSuperUser();
        $this->setUpWebsites();
        $this->setUpGoals();
        $this->trackSomeVisits();
    }
    public function tearDown(): void
    {
        // empty
    }
    private function setUpWebsites()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite('2021-01-01');
        }
    }

    protected function setUpGoals(): void
    {
        \Piwik\Plugins\Goals\API::getInstance()->addGoal(
            $this->idSite,
            'Random Goal',
            'url',
            'unmatchable',
            'contains',
            false,
            '0.10'
        );
    }

    protected function trackSomeVisits(): void
    {
        // page view, without following action
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1)->getDatetime());
        $t->setUrl('https://my.web.site/exit');
        $t->doTrackPageView('Exit Page');

        // page view followd by a site search
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.02)->getDatetime());
        $t->setUrl('https://my.web.site/exit_before_search');
        $t->doTrackPageView('Exit Page before Site Search');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.05)->getDatetime());
        $t->doTrackSiteSearch('random keyword');

        // page view followed by a content impression
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(3.2)->getDatetime());
        $t->setUrl('https://my.web.site/exit_before_content');
        $t->doTrackPageView('Exit Page before Content Impression');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(3.25)->getDatetime());
        $t->doTrackContentImpression('Advertisment', 'CTA', 'Partner Site');

        // page view followed by an event
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(4.6)->getDatetime());
        $t->setUrl('https://my.web.site/exit_before_event');
        $t->doTrackPageView('Exit Page before Event');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(4.65)->getDatetime());
        $t->doTrackEvent('Category', 'Action', 'Name');

        // page view followed by an outlink
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(5)->getDatetime());
        $t->setUrl('https://my.web.site/exit_before_outlink');
        $t->doTrackPageView('Exit Page before Outlink');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(5.03)->getDatetime());
        $t->doTrackAction('http://out.link/', 'link');

        // page view follow by a download
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(6.22)->getDatetime());
        $t->setUrl('https://my.web.site/exit_before_download');
        $t->doTrackPageView('Exit Page before Download');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(6.29)->getDatetime());
        $t->doTrackAction('http://my.web.site/download', 'download');

        // page view follow by a goal conversion
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(7)->getDatetime());
        $t->setUrl('https://my.web.site/exit_before_goal');
        $t->doTrackPageView('Exit Page before Goal');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(7.01)->getDatetime());
        $t->doTrackGoal(1, 0.22);

        // page view with included site search
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(7.6)->getDatetime());
        $t->setUrl('https://my.web.site/exit_with_search?k=keyword');
        $t->doTrackPageView('Exit Page with Site Search');
    }
}
