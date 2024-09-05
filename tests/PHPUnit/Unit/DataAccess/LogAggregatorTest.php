<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataAccess;

use Piwik\DataAccess\ArchivingDbAdapter;
use Piwik\DataAccess\LogAggregator;

/**
 * @group Core
 */
class LogAggregatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getTestQueryConversionsByDimensionForcingIndexFlagTestData
     *
     * @param bool $forceIndex
     * @return void
     */
    public function testQueryConversionsByDimensionForcingIndexFlag(bool $forceIndex)
    {
        $dimensions = ['custom_var_k1', 'custom_var_v1'];
        $where = "%s.custom_var_k1 != ''";

        $expectedSelect = "log_conversion.idgoal AS `idgoal`, 
			log_conversion.custom_var_k1 AS `custom_var_k1`, 
			log_conversion.custom_var_v1 AS `custom_var_v1`, 
			count(*) AS `1`, 
			count(distinct log_conversion.idvisit) AS `3`, 
			ROUND(SUM(log_conversion.revenue),2) AS `2`, 
			ROUND(SUM(log_conversion.revenue_subtotal),2) AS `4`, 
			ROUND(SUM(log_conversion.revenue_tax),2) AS `5`, 
			ROUND(SUM(log_conversion.revenue_shipping),2) AS `6`, 
			ROUND(SUM(log_conversion.revenue_discount),2) AS `7`, 
			SUM(log_conversion.items) AS `8`";
        $expectedFrom = $forceIndex
            ? [['table' => LogAggregator::LOG_CONVERSION_TABLE, 'useIndex' => 'index_idsite_datetime']]
            : [LogAggregator::LOG_CONVERSION_TABLE];
        $expectedWhere = "log_conversion.server_time >= ?
				AND log_conversion.server_time <= ?
				AND log_conversion.idsite IN (?) AND log_conversion.custom_var_k1 != ''";

        $dbMock = $this->createMock(ArchivingDbAdapter::class);
        $dbMock->expects($this->once())->method('query');

        $aggregatorMock = $this->createPartialMock(LogAggregator::class, ['generateQuery', 'getDb']);
        $aggregatorMock->expects($this->once())->method('generateQuery')->with($expectedSelect, $expectedFrom, $expectedWhere)->willReturn(['sql' => '', 'bind' => []]);
        $aggregatorMock->expects($this->once())->method('getDb')->willReturn($dbMock);
        $aggregatorMock->setSites([1]);
        $aggregatorMock->queryConversionsByDimension($dimensions, $where, [], [], false, false, $forceIndex);
    }

    public function getTestQueryConversionsByDimensionForcingIndexFlagTestData(): array
    {
        return [
            [false],
            [true],
        ];
    }
}
