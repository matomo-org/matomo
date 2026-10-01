<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Fixtures;

use Piwik\API\Request;
use Piwik\Date;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Tests\Framework\Fixture;

/**
 * One website with a page that is entered often and bounced from, which is what the
 * Heatmaps promotion is pitched on.
 *
 * This is the only promotion fixture here built on archived reports, and it is worth the
 * extra work because it is the only shape the others cannot show: a figure read from a
 * report, rendered as a link back to it, next to a page title a user of the instance chose.
 *
 * The visits are dated relative to the reporting week rather than to a fixed date, so the
 * fixture does not stop working when the week rolls over - the trap the seeded demo data
 * falls into every Monday.
 */
class SiteWithABouncingEntryPage extends Fixture
{
    public $idSite = 1;

    /**
     * Above the trigger's minimum entry visits, all of them bouncing.
     */
    public const ENTRY_VISITS = 210;

    public const PAGE_TITLE = 'Pricing';

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite('2020-01-01 00:00:00');
        }

        $this->trackBouncingVisits();

        // Build the archive the promotion will read. The trigger itself never archives, so
        // without this it would correctly report that there is nothing to read yet.
        Request::processRequest('Actions.getPageTitles', [
            'idSite' => $this->idSite,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
            'flat' => 1,
        ], []);
    }

    /**
     * Single page visits on one entry page, inside the last completed week.
     */
    private function trackBouncingVisits(): void
    {
        $start = Date::factory(ReportPeriod::DATE)->getDatetime();
        $tracker = self::getTracker($this->idSite, $start, true, true);
        $tracker->setUrl('http://example.org/pricing');

        for ($i = 0; $i < self::ENTRY_VISITS; $i++) {
            $tracker->setForceVisitDateTime(
                Date::factory($start)->addPeriod($i, 'minute')->getDatetime()
            );
            $tracker->setNewVisitorId();
            $tracker->setIp('10.30.' . (int) ($i / 250) . '.' . ($i % 250 + 1));
            self::checkResponse($tracker->doTrackPageView(self::PAGE_TITLE));
        }
    }

    public function tearDown(): void
    {
        // nothing to undo: the test database is rebuilt for the fixture
    }
}
