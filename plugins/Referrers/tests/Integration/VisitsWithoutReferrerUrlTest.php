<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\PrivacyManager\Dao\LogDataAnonymizer;
use Piwik\Plugins\Referrers\Archiver;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Referrers
 * @group VisitsWithoutReferrerUrlTest
 * @group Plugins
 */
class VisitsWithoutReferrerUrlTest extends IntegrationTestCase
{
    private const DATE = '2026-10-01';

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->idSite = Fixture::createWebsite('2026-01-01 00:00:00');

        $referrerUrls = ['https://twitter.com/matomo_org', 'https://example.org/page', 'https://chatgpt.com/'];
        foreach ($referrerUrls as $index => $referrerUrl) {
            $tracker = Fixture::getTracker($this->idSite, self::DATE . ' ' . (10 + $index) . ':00:00');
            $tracker->setIp('10.0.0.' . ($index + 1));
            $tracker->setForceNewVisit();
            $tracker->setUrlReferrer($referrerUrl);
            Fixture::checkResponse($tracker->doTrackPageView('Page ' . $index));
        }

        // GDPR tools: unset the referrer URL of previously tracked visits
        (new LogDataAnonymizer())->unsetLogVisitTableColumns(
            [$this->idSite],
            self::DATE . ' 00:00:00',
            self::DATE . ' 23:59:59',
            ['referer_url']
        );
    }

    public function testReferrerReportsAreArchivedForVisitsWithoutReferrerUrl(): void
    {
        $this->assertSame(3, (int) $this->getReport('VisitsSummary.get')->getFirstRow()->getColumn('nb_visits'));
        $this->assertSame(1, (int) $this->getReport('Referrers.getSocials')->getRowFromLabel('Twitter')->getColumn('nb_visits'));
        $this->assertSame(1, (int) $this->getReport('Referrers.getWebsites')->getRowFromLabel('example.org')->getColumn('nb_visits'));
    }

    public function testVisitsWithoutReferrerUrlAreNotReportedByReferrerUrl(): void
    {
        $this->assertNull($this->getReport('Referrers.getSocials')->getRowFromLabel('Twitter')->getIdSubDataTable());
        $this->assertNull($this->getReport('Referrers.getWebsites')->getRowFromLabel('example.org')->getIdSubDataTable());

        foreach (['day', 'week'] as $period) {
            $distinctUrls = $this->getReport('Referrers.getNumberOfDistinctWebsitesUrls', $period)->getFirstRow()->getColumn(Archiver::METRIC_DISTINCT_URLS_RECORD_NAME);
            $this->assertSame(0, (int) $distinctUrls, $period);
        }
    }

    public function testVisitsLogListsVisitsWithoutReferrerUrl(): void
    {
        /** @var DataTable $visits */
        $visits = Request::processRequest('Live.getLastVisitsDetails', [
            'idSite' => $this->idSite,
            'period' => 'day',
            'date' => self::DATE,
            'filter_sort_order' => 'asc',
            'filter_limit' => -1,
        ]);

        $referrers = array_map(null, $visits->getColumn('referrerType'), $visits->getColumn('referrerName'));

        $this->assertSame([['social', 'Twitter'], ['website', 'example.org'], ['ai', 'ChatGPT']], $referrers);
    }

    private function getReport(string $method, string $period = 'day'): DataTable
    {
        return Request::processRequest($method, [
            'idSite' => $this->idSite,
            'period' => $period,
            'date' => self::DATE,
        ]);
    }
}
