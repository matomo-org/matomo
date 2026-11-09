<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Segment;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * A segment over an expression of visit columns, eg FLOOR(log_visit.visitor_seconds_since_order / 86400),
 * is compared directly when excluding values. These tests check that the visits matched are the ones an
 * idvisit subquery matched, including visits where the expression is NULL.
 *
 * @group Segment
 * @group VisitExpressionNotEqualsTest
 */
class VisitExpressionNotEqualsTest extends IntegrationTestCase
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

        // days since the last ecommerce order: never ordered (NULL), ordered today (0), ordered two days ago (2)
        foreach ([null, 0, 2 * 86400] as $index => $secondsSinceOrder) {
            $tracker = Fixture::getTracker($this->idSite, self::DATE . ' ' . (10 + $index) . ':00:00');
            $tracker->setIp('10.0.0.' . ($index + 1));
            $tracker->setForceNewVisit();
            Fixture::checkResponse($tracker->doTrackPageView('Page ' . $index));

            Db::query(
                'UPDATE ' . Common::prefixTable('log_visit') . ' SET visitor_seconds_since_order = ? WHERE idvisit = ?',
                [$secondsSinceOrder, $index + 1]
            );
        }
    }

    /**
     * @dataProvider getSegmentsAndExpectedVisits
     */
    public function testNotEqualsOnAVisitExpressionMatchesTheSameVisitsAsBefore(string $segment, int $expectedVisits): void
    {
        $this->assertSame($expectedVisits, $this->getArchivedVisitCount($segment));
    }

    public function getSegmentsAndExpectedVisits(): iterable
    {
        yield 'no segment' => ['', 3];

        // the visit that never ordered has no value, so it is kept when a value is excluded
        yield 'not 0 keeps the visit without a value' => ['daysSinceLastEcommerceOrder!=0', 2];
        yield 'not 2 keeps the visit without a value' => ['daysSinceLastEcommerceOrder!=2', 2];
        yield 'equals 0' => ['daysSinceLastEcommerceOrder==0', 1];

        yield 'hour of the visit excluded' => ['visitServerHour!=10', 2];
        yield 'hour not on any visit' => ['visitServerHour!=3', 3];
    }

    private function getArchivedVisitCount(string $segment): int
    {
        /** @var DataTable $table */
        $table = Request::processRequest('VisitsSummary.get', [
            'idSite' => $this->idSite,
            'period' => 'day',
            'date' => self::DATE,
            'segment' => $segment,
        ]);

        $row = $table->getFirstRow();

        return $row ? (int) $row->getColumn('nb_visits') : 0;
    }
}
