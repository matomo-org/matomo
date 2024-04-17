<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary\tests\Unit\Reports;

use Piwik\DataTable;
use Piwik\Plugins\VisitsSummary\Reports\Get;

/**
 * @group VisitsSummary
 * @group Reports
 * @group GetTest
 * @group Plugins
 */
class GetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Get
     */
    private $get;

    private $column = 'nb_users';

    public function setUp(): void
    {
        parent::setUp();
        $this->get = new Get();
    }

    public function test_removeUsersFromProcessedReport_shouldNotDoAnything_IfNothingRelatedToUsersIsGiven()
    {
        $response = array();
        $this->get->removeUsersFromProcessedReport($response);
        $this->assertSame(array(), $response);

        $response = array($this->column => '10', 'test' => 'whatever', 'columns' => array($this->column));
        $this->get->removeUsersFromProcessedReport($response);
        $this->assertSame(array($this->column => '10', 'test' => 'whatever', 'columns' => array($this->column)), $response);
    }

    public function test_removeUsersFromProcessedReport_shouldRemoveMetrics_IfUserIsGiven()
    {
        $response = array('metadata' => array('metrics' => array('nb_visits' => 'Visits', $this->column => 'Users')));
        $this->get->removeUsersFromProcessedReport($response);
        $this->assertSame(array('metadata' => array('metrics' => array('nb_visits' => 'Visits'))), $response);
    }

    public function test_removeUsersFromProcessedReport_shouldRemoveMetricsDocumentation_IfUserIsGiven()
    {
        $response = array('metadata' => array('metricsDocumentation' => array('nb_visits' => 'Visits', $this->column => 'Users')));
        $this->get->removeUsersFromProcessedReport($response);
        $this->assertSame(array('metadata' => array('metricsDocumentation' => array('nb_visits' => 'Visits'))), $response);
    }

    public function test_removeUsersFromProcessedReport_shouldRemoveColumn_IfUserIsGiven()
    {
        $response = array('columns' => array('nb_visits' => 'Visits', $this->column => 'Users'));
        $this->get->removeUsersFromProcessedReport($response);
        $this->assertSame(array('columns' => array('nb_visits' => 'Visits')), $response);
    }

    public function test_removeUsersFromProcessedReport_shouldRemoveUsersColumnFromDataTable_IfUserIsGiven()
    {
        $table = $this->getDataTableWithUsers();
        $this->assertSame(array(20), $table->getColumn($this->column)); // verify column present

        $response = array('reportData' => $table);
        $this->get->removeUsersFromProcessedReport($response);

        $this->assertSame(array(false), $table->getColumn($this->column));
        $this->assertSame(array(10), $table->getColumn('nb_visits'));
    }

    public function test_removeUsersFromProcessedReport_shouldRemoveUsersColumnFromDataTableMap_IfUserIsGiven()
    {
        $table = new DataTable\Map();
        $table->addTable($this->getDataTableWithUsers(), 'label');
        $this->assertSame(array(20), $table->getColumn($this->column)); // verify column present

        $response = array('reportData' => $table);
        $this->get->removeUsersFromProcessedReport($response);

        $this->assertSame(array(false), $table->getColumn($this->column));
        $this->assertSame(array(10), $table->getColumn('nb_visits'));
    }

    private function getDataTableWithUsers()
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(array('nb_visits' => 10, $this->column => 20));

        return $table;
    }
}
