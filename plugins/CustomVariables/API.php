<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\API\Request;
use Piwik\Archive;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Piwik;
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

    /**
     * Get a list of all available custom variable slots (scope + index) and which names have been used so far in
     * each slot since the beginning of the website.
     *
     * @param int $idSite
     * @return array
     */
    public function getUsagesOfSlots($idSite)
    {
        Piwik::checkUserHasAdminAccess($idSite);

        $numVars = CustomVariables::getNumUsableCustomVariables();

        $usedCustomVariables = array(
            'visit' => array_fill(1, $numVars, array()),
            'page'  => array_fill(1, $numVars, array()),
        );

        /** @var DataTable $customVarUsages */
        $today = StaticContainer::get('CustomVariables.today');
        $date = '2008-12-12,' . $today;
        $customVarUsages = Request::processRequest('CustomVariables.getCustomVariables',
            array('idSite' => $idSite, 'period' => 'range', 'date' => $date,
                  'format' => 'original')
        );

        foreach ($customVarUsages->getRows() as $row) {
            $slots = $row->getMetadata('slots');

            if (!empty($slots)) {
                foreach ($slots as $slot) {
                    $usedCustomVariables[$slot['scope']][$slot['index']][] = array(
                        'name' => $row->getColumn('label'),
                        'nb_visits' => $row->getColumn('nb_visits'),
                        'nb_actions' => $row->getColumn('nb_actions'),
                    );
                }
            }
        }

        $grouped = array();
        foreach ($usedCustomVariables as $scope => $scopes) {
            foreach ($scopes as $index => $cvars) {
                $grouped[] = array(
                    'scope' => $scope,
                    'index' => $index,
                    'usages' => $cvars
                );
            }
        }

        return $grouped;
    }
}

