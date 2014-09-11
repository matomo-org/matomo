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
use Piwik\Piwik;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;

/**
 * The Custom Variables API lets you access reports for your <a href='http://piwik.org/docs/custom-variables/' target='_blank'>Custom Variables</a> names and values.
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
    protected function getDataTable($idSite, $period, $date, $segment, $expanded, $idSubtable)
    {
        $dataTable = Archive::getDataTableFromArchive(Archiver::CUSTOM_VARIABLE_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_ACTIONS, 'desc', $naturalSort = false, $expanded));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ColumnDelete', 'nb_uniq_visitors');
        return $dataTable;
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param string|bool $segment
     * @param bool $expanded
     * @param bool $_leavePiwikCoreVariables
     *
     * @return DataTable|DataTable\Map
     */
    public function getCustomVariables($idSite, $period, $date, $segment = false, $expanded = false, $_leavePiwikCoreVariables = false)
    {
        $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded, $idSubtable = null);

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
        $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded = false, $idSubtable);

        if (!$_leavePriceViewedColumn) {
            $dataTable->deleteColumn('price_viewed');
        } else {
            // Hack Ecommerce product price tracking to display correctly
            $dataTable->renameColumn('price_viewed', 'price');
        }
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', function ($label) {
            return $label == \Piwik\Plugins\CustomVariables\Archiver::LABEL_CUSTOM_VALUE_NOT_DEFINED
                ? Piwik::translate('General_NotDefined', Piwik::translate('CustomVariables_ColumnCustomVariableValue'))
                : $label;
        }));
        return $dataTable;
    }
}

