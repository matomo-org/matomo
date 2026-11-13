<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\DataAccess\LogAggregator;
use Piwik\Metrics;
use Piwik\Plugins\Transitions\Model;
use Piwik\RankingQuery;

/**
 * The archiving queries rank tied rows by label, so the archived lists don't depend on the database's order of ties.
 *
 * @group Transitions
 * @group Plugins
 */
class ModelTest extends TestCase
{
    public function testTheArchivingActionQueriesOrderTiesByName(): void
    {
        $orders = [];
        $logAggregator = $this->createMock(LogAggregator::class);
        $logAggregator->method('queryActionsByDimension')->willReturnCallback(
            function ($dimensions, $where, $selects, $metrics, $rankingQuery, $joinLogActionOn, $secondaryOrderBy) use (&$orders) {
                $orders[] = $secondaryOrderBy;

                return [];
            }
        );

        $model = new Model();
        $model->queryInternalReferrersOfPages($logAggregator, [5], 1, RankingQuery::LABEL_SUMMARY_ROW);
        $model->queryFollowingActionsOfPages($logAggregator, [5], 1, RankingQuery::LABEL_SUMMARY_ROW);

        self::assertSame(['`name`, `url_prefix`', '`name`, `url_prefix`'], $orders);
    }

    public function testTheArchivingReferrersQueryOrdersTiesByReferrer(): void
    {
        $orders = [];
        $logAggregator = $this->createMock(LogAggregator::class);
        $logAggregator->method('queryVisitsByDimension')->willReturnCallback(
            function ($dimensions, $where, $selects, $metrics, $rankingQuery, $orderBy) use (&$orders) {
                $orders[] = $orderBy;

                return [];
            }
        );

        (new Model())->queryExternalReferrersOfPages($logAggregator, [5], 1, RankingQuery::LABEL_SUMMARY_ROW);

        self::assertSame(['`' . Metrics::INDEX_NB_VISITS . '` DESC, `referrer_data`'], $orders);
    }
}
