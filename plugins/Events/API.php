<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Archive;
use Piwik\Piwik;

/**
 * The Events API lets you request reports about your users' Custom Events.
 *
 * Events are tracked using the Javascript Tracker trackEvent() function, or using the [Tracking HTTP API](http://developer.matomo.org/api-reference/tracking-api).
 *
 * <br/>An event is defined by an event category (Videos, Music, Games...),
 * an event action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...),
 * and an optional event name (a movie name, a song title, etc.) and an optional numeric value.
 *
 * <br/>This API exposes the following Custom Events reports: `getCategory` lists the top Event Categories,
 * `getAction` lists the top Event Actions, `getName` lists the top Event Names.
 *
 * <br/>These Events report define the following metrics: nb_uniq_visitors, nb_visits, nb_events.
 * If you define values for your events, you can expect to see the following metrics: nb_events_with_value,
 * sum_event_value, min_event_value, max_event_value, avg_event_value
 *
 * <br/>The Events.get* reports can be used with an optional `&secondaryDimension` parameter.
 * Secondary dimension is the dimension used in the sub-table of the Event report you are requesting.
 *
 * <br/>Here are the possible values of `secondaryDimension`: <ul>
 * <li>For `Events.getCategory` you can set `secondaryDimension` to `eventAction` or `eventName`.</li>
 * <li>For `Events.getAction` you can set `secondaryDimension` to `eventName` or `eventCategory`.</li>
 * <li>For `Events.getName` you can set `secondaryDimension` to `eventAction` or `eventCategory`.</li>
 * </ul>
 *
 * <br/>For example, to request all Custom Events Categories, and for each, the top Event actions,
 * you would request: `method=Events.getCategory&secondaryDimension=eventAction&flat=1`.
 * You may also omit `&flat=1` in which case, to get top Event actions for one Event category,
 * use `method=Events.getActionFromCategoryId` passing it the `&idSubtable=` of this Event category.
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
        if (isset($this->defaultMappingApiToSecondaryDimension[$apiMethod])) {
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
        if (!is_array($record)) {
            return $record;
        }
        // when secondaryDimension is incorrectly set
        if (empty($record[$secondaryDimension])) {
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
        if (!is_array($records)) {
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

    protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded = false, $idSubtable = null, $secondaryDimension = false, $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $this->checkSecondaryDimension($name, $secondaryDimension);
        $recordName = $this->getRecordNameForAction($name, $secondaryDimension);

        $dataTable = Archive::createDataTableFromArchive($recordName, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);

        if ($flat) {
            $dataTable->filterSubtables('Piwik\Plugins\Events\DataTable\Filter\ReplaceEventNameNotSet');
        } else {
            $dataTable->filter('AddSegmentValue', array(function ($label) {
                if ($label === Archiver::EVENT_NAME_NOT_SET) {
                    return false;
                }

                return $label;
            }));
        }

        $dataTable->filter('Piwik\Plugins\Events\DataTable\Filter\ReplaceEventNameNotSet');

        return $dataTable;
    }

    public function getCategory($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false, $flat = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension, $flat);
    }

    public function getAction($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false, $flat = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension, $flat);
    }

    public function getName($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false, $flat = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension, $flat);
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
}