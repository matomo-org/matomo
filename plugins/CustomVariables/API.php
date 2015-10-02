<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;

/**
 * The Custom Variables API lets you access reports for your <a href='http://piwik.org/docs/custom-variables/' rel='noreferrer' target='_blank'>Custom Variables</a> names and values.
 *
 * @method static \Piwik\Plugins\CustomVariables\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param string $segment
     * @param bool $expanded
     * @param int $idSubtable
     *
     * @return DataTable|DataTable\Map
     */
    protected function getDataTable($idSite, $period, $date, $segment, $expanded, $flat, $idSubtable)
    {
        $dataTable = Archive::createDataTableFromArchive(Archiver::CUSTOM_VARIABLE_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        $dataTable->queueFilter('ColumnDelete', 'nb_uniq_visitors');

        if ($flat) {
            $dataTable->filterSubtables('Sort', array(Metrics::INDEX_NB_ACTIONS, 'desc', $naturalSort = false, $expanded));
            $dataTable->queueFilterSubtables('ColumnDelete', 'nb_uniq_visitors');
        }

        return $dataTable;
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param string|bool $segment
     * @param bool $expanded
     * @param bool $_leavePiwikCoreVariables
     * @param bool $flat
     *
     * @return DataTable|DataTable\Map
     */
    public function getCustomVariables($idSite, $period, $date, $segment = false, $expanded = false, $_leavePiwikCoreVariables = false, $flat = false)
    {
        $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded, $flat, $idSubtable = null);

        if ($dataTable instanceof DataTable
            && !$_leavePiwikCoreVariables
        ) {
            $mapping = self::getReservedCustomVariableKeys();
            foreach ($mapping as $name) {
                $row = $dataTable->getRowFromLabel($name);
                if ($row) {
                    $dataTable->deleteRow($dataTable->getRowIdFromLabel($name));
                }
            }
        }

        if ($flat) {
            $dataTable->filterSubtables('Piwik\Plugins\CustomVariables\DataTable\Filter\CustomVariablesValuesFromNameId');
        } else {
            $dataTable->filter('AddSegmentByLabel', array('customVariableName'));
        }

        return $dataTable;
    }

    /**
     * @ignore
     * @return array
     */
    public static function getReservedCustomVariableKeys()
    {
        return array('_pks', '_pkn', '_pkc', '_pkp', ActionSiteSearch::CVAR_KEY_SEARCH_COUNT, ActionSiteSearch::CVAR_KEY_SEARCH_CATEGORY);
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param int $idSubtable
     * @param string|bool $segment
     * @param bool $_leavePriceViewedColumn
     *
     * @return DataTable|DataTable\Map
     */
    public function getCustomVariablesValuesFromNameId($idSite, $period, $date, $idSubtable, $segment = false, $_leavePriceViewedColumn = false)
    {
        $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable);

        if (!$_leavePriceViewedColumn) {
            $dataTable->deleteColumn('price_viewed');
        } else {
            // Hack Ecommerce product price tracking to display correctly
            $dataTable->renameColumn('price_viewed', 'price');
        }
        $dataTable->filter('Piwik\Plugins\CustomVariables\DataTable\Filter\CustomVariablesValuesFromNameId');

        return $dataTable;
    }
}

