<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Unit\API;

use Piwik\API\DataTableGenericFilter;
use Piwik\DataTable;

class DataTableGenericFilterTest extends \PHPUnit\Framework\TestCase
{
    public function test_genericFiltersToDisableMetadata_shouldBeRespected()
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['nb_visits' => 2, 'nb_actions' => 3],
            ['nb_visits' => 4, 'nb_actions' => 5],
        ]);
        $dataTable->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, ['Limit']);

        $genericFilter = new DataTableGenericFilter(['filter_limit' => 1], null);
        $genericFilter->filter($dataTable);

        $this->assertEquals(2, $dataTable->getRowsCount());
    }
}
