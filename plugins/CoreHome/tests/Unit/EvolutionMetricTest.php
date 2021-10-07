<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreHome\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Plugins\CoreHome\Columns\Metrics\EvolutionMetric;

/**
 * @group EvolutionMetric
 */
class EvolutionMetricTest extends TestCase
{
    public function test_shouldDoProportionalComparision_ifCurentPeriodIncomplete()
    {
        $currentData = new DataTable();
        $cPeriod = new \Piwik\Period\Week(Date::factory('2021-10-10'));
        $currentData->setMetadata('period', $cPeriod);

        // If the archived date meta data value exists on the current datatable then it will be used
        // as the current date for calculation purposes, we can use this to consistently test the
        // ratio calculation by supplying a fixed set of dates that should result in a 0.5 ratio
        $halfWeekTS = mktime(0,0,0,10,7,2021);
        $currentData->setMetaData(DataTable::ARCHIVED_DATE_METADATA_NAME, $halfWeekTS);

        $pastData = new DataTable();
        $sPeriod = new \Piwik\Period\Week(Date::factory('2021-10-03'));
        $pastData->setMetadata('period', $sPeriod);

        $ratio = EvolutionMetric::getRatio($currentData, $pastData);

        $this->assertEquals(0.5, $ratio);
    }
}
