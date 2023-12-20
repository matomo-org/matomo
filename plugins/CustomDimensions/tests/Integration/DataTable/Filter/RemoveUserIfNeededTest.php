<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomDimensions
 * @group DimensionTest
 * @group Dimension
 * @group Dao
 * @group Plugins
 */
class RemoveUserIfNeededTest extends IntegrationTestCase
{
    private $filter = 'Piwik\Plugins\CustomDimensions\DataTable\Filter\RemoveUserIfNeeded';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2010-01-01 00:00:00');
    }

    public function test_filter_shouldNotRemoveColumn_IfThereIsAValueInTableForNbUsers()
    {
        $columns = $this->filterTable($withUser = 5);

        $this->assertSame(array(0, false, 5), $columns);
    }

    public function test_filter_withoutUsers_shouldRemoveColumn()
    {
        $columns = $this->filterTable($withUser = 0);
        $this->assertSame(array(false, false, false), $columns);
    }

    private function filterTable($withUser = 5)
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'val1', Metrics::INDEX_NB_USERS => 0)),
            array(Row::COLUMNS => array('label' => 'val2')),
            array(Row::COLUMNS => array('label' => 'val2 5w ö?', Metrics::INDEX_NB_USERS => $withUser))
        ));

        $dataTable->filter($this->filter, array($idSite = 1, $period = 'day', $date = 'today'));

        return $dataTable->getColumn(Metrics::INDEX_NB_USERS);
    }
}
