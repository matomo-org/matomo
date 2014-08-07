<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleReport;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * API for plugin ExampleReport
 *
 * @method static \Piwik\Plugins\ExampleReport\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getExampleReport($idSite, $period, $date, $segment = false)
    {
        $table = new DataTable();

        $table->addRowFromArray(array(Row::COLUMNS => array('nb_visits' => 5)));

        return $table;
    }
}
