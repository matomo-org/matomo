<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * Custom Events API
 *
 * @package Events
 * @method static \Piwik\Plugins\Events\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected $defaultMappingApiToSecondaryDimension = array(
        'getCategory' => 'eventAction',
        'getAction'   => 'eventName',
        'getName'     => 'eventAction',
    );

    protected $mappingApiToRecord = array(
        'getCategory'             =>
            array(
                'eventAction' => Archiver::EVENTS_CATEGORY_ACTION_RECORD_NAME,
                'eventName'   => Archiver::EVENTS_CATEGORY_NAME_RECORD_NAME,
            ),
        'getAction'               =>
            array(
                'eventName'     => Archiver::EVENTS_ACTION_NAME_RECORD_NAME,
                'eventCategory' => Archiver::EVENTS_ACTION_CATEGORY_RECORD_NAME,
            ),
        'getName'                 =>
            array(
                'eventAction'   => Archiver::EVENTS_NAME_ACTION_RECORD_NAME,
                'eventCategory' => Archiver::EVENTS_NAME_CATEGORY_RECORD_NAME,
            ),
        'getActionFromCategoryId' => Archiver::EVENTS_CATEGORY_ACTION_RECORD_NAME,
        'getNameFromCategoryId'   => Archiver::EVENTS_CATEGORY_NAME_RECORD_NAME,
        'getCategoryFromActionId' => Archiver::EVENTS_ACTION_CATEGORY_RECORD_NAME,
        'getNameFromActionId'     => Archiver::EVENTS_ACTION_NAME_RECORD_NAME,
        'getActionFromNameId'     => Archiver::EVENTS_NAME_ACTION_RECORD_NAME,
        'getCategoryFromNameId'   => Archiver::EVENTS_NAME_CATEGORY_RECORD_NAME,
    );

    /**
     * @ignore
     */
    public function getActionToLoadSubtables($apiMethod, $secondaryDimension = false)
    {
        $recordName = $this->getRecordNameForAction($apiMethod, $secondaryDimension);
        $apiMethod = array_search( $recordName, $this->mappingApiToRecord );
        return $apiMethod;
    }

    /**
     * @ignore
     */
    public function getDefaultSecondaryDimension($apiMethod)
    {
        if(isset($this->defaultMappingApiToSecondaryDimension[$apiMethod])) {
            return $this->defaultMappingApiToSecondaryDimension[$apiMethod];
        }
        return false;
    }


    protected function getRecordNameForAction($apiMethod, $secondaryDimension = false)
    {
        if (empty($secondaryDimension)) {
            $secondaryDimension = $this->getDefaultSecondaryDimension($apiMethod);
        }
        $record = $this->mappingApiToRecord[$apiMethod];
        if(!is_array($record)) {
            return $record;
        }
        // when secondaryDimension is incorrectly set
        if(empty($record[$secondaryDimension])) {
            return key($record);
        }
        return $record[$secondaryDimension];
    }

    /**
     * @ignore
     * @param $apiMethod
     * @return array
     */
    public function getSecondaryDimensions($apiMethod)
    {
        $records = $this->mappingApiToRecord[$apiMethod];
        if(!is_array($records)) {
            return false;
        }
        return array_keys($records);
    }

    protected function checkSecondaryDimension($apiMethod, $secondaryDimension)
    {
        if (empty($secondaryDimension)) {
            return;
        }

        $isSecondaryDimensionValid =
            isset($this->mappingApiToRecord[$apiMethod])
            && isset($this->mappingApiToRecord[$apiMethod][$secondaryDimension]);

        if (!$isSecondaryDimensionValid) {
            throw new \Exception(
                "Secondary dimension '$secondaryDimension' is not valid for the API $apiMethod. ".
                "Use one of: " . implode(", ", $this->getSecondaryDimensions($apiMethod))
            );
        }
    }

    protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded = false, $idSubtable = null, $secondaryDimension = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $this->checkSecondaryDimension($name, $secondaryDimension);
        $recordName = $this->getRecordNameForAction($name, $secondaryDimension);
        $dataTable = Archive::getDataTableFromArchive($recordName, $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterDataTable($dataTable);
        return $dataTable;
    }

    public function getCategory($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension);
    }

    public function getAction($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension);
    }

    public function getName($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension);
    }

    public function getActionFromCategoryId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getNameFromCategoryId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getCategoryFromActionId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getNameFromActionId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getActionFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getCategoryFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    /**
     * @param DataTable $dataTable
     */
    protected function filterDataTable($dataTable)
    {
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->filter(function (DataTable $table) {
            $row = $table->getRowFromLabel(Archiver::EVENT_NAME_NOT_SET);
            if ($row) {
                $row->setColumn('label', Piwik::translate('General_NotDefined', Piwik::translate('Events_EventName')));
            }
        });

        // add processed metric avg_event_value
        $dataTable->queueFilter('ColumnCallbackAddColumnQuotient',
            array('avg_event_value',
                  'sum_event_value',
                  'nb_events_with_value',
                  $precision = 2,
                  $shouldSkipRows = true)
        );
    }
}