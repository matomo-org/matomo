<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Fixtures;

use Piwik\Access;
use Piwik\Plugins\ScheduledReports\API as ScheduledReportsApi;
use Piwik\Tests\Framework\Fixture;

/**
 * One website with enough scheduled reports to make the second of the two Custom Reports
 * promotions fire.
 *
 * Custom Reports is promoted by two different triggers with different copy, and this is the
 * other one - the segments fixture covers the first. Like it, this needs no archived data,
 * so the promotion does not stop appearing when the reporting week rolls over.
 */
class SiteWithScheduledReports extends Fixture
{
    public $idSite = 1;

    public $dateTime = '2026-01-10 09:00:00';

    /**
     * One more than
     * {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger::MINIMUM_REPORTS}
     * asks for.
     */
    public const REPORTS = 4;

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite('2026-01-01 00:00:00');
        }

        Access::doAsSuperUser(function () {
            for ($i = 1; $i <= self::REPORTS; $i++) {
                ScheduledReportsApi::getInstance()->addReport(
                    $this->idSite,
                    'Weekly summary ' . $i,
                    'week',
                    0,
                    'email',
                    'pdf',
                    ['VisitsSummary_get'],
                    ['displayFormat' => 1, 'emailMe' => true, 'evolutionGraphsWithinPeriod' => false]
                );
            }
        });

        $this->trackOneVisit();
    }

    /**
     * A website with no data at all is sent to the tracking-code page instead of the
     * dashboard, and the banner under test is never rendered.
     */
    private function trackOneVisit(): void
    {
        $tracker = self::getTracker($this->idSite, $this->dateTime, true, true);
        $tracker->setUrl('http://example.org/');
        self::checkResponse($tracker->doTrackPageView('Home'));
    }

    public function tearDown(): void
    {
        // nothing to undo: the test database is rebuilt for the fixture
    }
}
