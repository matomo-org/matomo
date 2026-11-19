<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\Fixture;

/**
 * Two sites with goals. On the first site "Goal 10" and "Demo request" are starred, on the second
 * site no goal is starred. Every goal of the first site has conversions.
 */
class StarredGoals extends Fixture
{
    public $dateTime = '2015-03-04 00:00:00';
    public $idSite = 1;
    public $idSiteWithoutStarredGoals = 2;

    /**
     * Names by goal ID. Sorted by name: apple pay, contact form, Demo request, Download, Goal 2, Goal 10, Newsletter.
     *
     * @var array<int, string>
     */
    public $goalNames = [
        1 => 'Newsletter',
        2 => 'contact form',
        3 => 'Goal 10',
        4 => 'Goal 2',
        5 => 'Demo request',
        6 => 'Download',
        7 => 'apple pay',
    ];

    /**
     * @var list<int>
     */
    public $starredIdGoals = [3, 5];

    public function setUp(): void
    {
        Fixture::createSuperUser();

        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime, $ecommerce = 0, 'Site with starred goals');
        }

        if (!self::siteCreated($this->idSiteWithoutStarredGoals)) {
            self::createWebsite($this->dateTime, $ecommerce = 0, 'Site without starred goals');
        }

        $api = GoalsApi::getInstance();

        foreach ($this->goalNames as $idGoal => $name) {
            if (!self::goalExists($this->idSite, $idGoal)) {
                $api->addGoal($this->idSite, $name, 'url', '/goal-page-' . $idGoal . '/', 'contains', false, 5);
            }
        }

        // the same goal IDs as on the first site
        foreach ([1 => 'Newsletter', 2 => 'Beta', 3 => 'Alpha'] as $idGoal => $name) {
            if (!self::goalExists($this->idSiteWithoutStarredGoals, $idGoal)) {
                $api->addGoal($this->idSiteWithoutStarredGoals, $name, 'url', '/goal-page-' . $idGoal . '/', 'contains');
            }
        }

        foreach ($this->starredIdGoals as $idGoal) {
            $api->starGoal($this->idSite, $idGoal);
        }

        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function trackVisits(): void
    {
        $referrers = ['', 'http://www.google.com/search?q=starred+goals', 'http://example.org/a-page'];

        // all visits are sent in one request
        $tracker = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $tracker->setTokenAuth(self::getTokenAuth());
        $tracker->enableBulkTracking();

        // one visitor per conversion, the goal with the ID n is converted by n visitors
        $visitor = 0;
        foreach (array_keys($this->goalNames) as $idGoal) {
            for ($i = 0; $i < $idGoal; $i++) {
                $visitor++;

                $tracker->setNewVisitorId();
                $tracker->setIp('156.5.3.' . $visitor);
                $tracker->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1 + $visitor * 0.1)->getDatetime());
                $tracker->setUrlReferrer($referrers[$visitor % count($referrers)]);
                $tracker->setUrl('http://example.org/goal-page-' . $idGoal . '/');
                $tracker->doTrackPageView('Goal page ' . $idGoal);
            }
        }

        self::checkBulkTrackingResponse($tracker->doBulkTrack());
    }
}
