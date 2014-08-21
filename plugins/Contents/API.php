<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * API for plugin Contents
 *
 * @method static \Piwik\Plugins\Contents\API getInstance()
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
    public function getContents($idSite, $period, $date, $segment = false)
    {
        $table = new DataTable();

        $table->addRowFromArray(array(Row::COLUMNS => array(
            'label' => 'My banner',
            'nb_impressions' => 50,
            'nb_conversions' => 5,
            'conversion_rate' => '10%'
        )));

        return $table;
    }
}
