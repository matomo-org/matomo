<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Events;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * The Events API lets you request reports about your users' Custom Events.
 *
 * Events are tracked using the Javascript Tracker trackEvent() function, or using the [Tracking HTTP API](https://developer.matomo.org/api-reference/tracking-api).
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
        $apiMethod = array_search($recordName, $this->mappingApiToRecord);
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
                "Secondary dimension '$secondaryDimension' is not valid for the API $apiMethod. " .
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

        $dataTable->filter(function ($dataTable) {
            $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, [
                Metrics::INDEX_EVENT_MIN_EVENT_VALUE => 'min',
                Metrics::INDEX_EVENT_MAX_EVENT_VALUE => 'max',
            ]);
        });

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

    /**
     * Returns the Custom Event categories report.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @param bool $expanded If true, loads all subtables for each row.
     * @param 'eventAction'|'eventName'|false $secondaryDimension Secondary dimension for subtables, or false to use the default.
     * @param bool $flat If true, returns a flattened table and disables recursive filters; implies expanded behavior.
     * @return DataTable|DataTable\Map Event categories report, or a map when multiple sites/periods are requested.
     */
    public function getCategory($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false, $flat = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension, $flat);
    }

    /**
     * Returns the Custom Event actions report.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @param bool $expanded If true, loads all subtables for each row.
     * @param 'eventName'|'eventCategory'|false $secondaryDimension Secondary dimension for subtables, or false to use the default.
     * @param bool $flat If true, returns a flattened table and disables recursive filters; implies expanded behavior.
     * @return DataTable|DataTable\Map Event actions report, or a map when multiple sites/periods are requested.
     */
    public function getAction($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false, $flat = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension, $flat);
    }

    /**
     * Returns the Custom Event names report.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @param bool $expanded If true, loads all subtables for each row.
     * @param 'eventAction'|'eventCategory'|false $secondaryDimension Secondary dimension for subtables, or false to use the default.
     * @param bool $flat If true, returns a flattened table and disables recursive filters; implies expanded behavior.
     * @return DataTable|DataTable\Map Event names report, or a map when multiple sites/periods are requested.
     */
    public function getName($idSite, $period, $date, $segment = false, $expanded = false, $secondaryDimension = false, $flat = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded, $idSubtable = false, $secondaryDimension, $flat);
    }

    /**
     * Returns the Custom Event actions for a specific category subtable.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param int|string $idSubtable Numeric subtable ID, or 'all' to load all subtables.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @return DataTable|DataTable\Map Event actions for the requested category, or a map when multiple sites/periods are requested.
     */
    public function getActionFromCategoryId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    /**
     * Returns the Custom Event names for a specific category subtable.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param int|string $idSubtable Numeric subtable ID, or 'all' to load all subtables.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @return DataTable|DataTable\Map Event names for the requested category, or a map when multiple sites/periods are requested.
     */
    public function getNameFromCategoryId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    /**
     * Returns the Custom Event categories for a specific action subtable.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param int|string $idSubtable Numeric subtable ID, or 'all' to load all subtables.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @return DataTable|DataTable\Map Event categories for the requested action, or a map when multiple sites/periods are requested.
     */
    public function getCategoryFromActionId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    /**
     * Returns the Custom Event names for a specific action subtable.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param int|string $idSubtable Numeric subtable ID, or 'all' to load all subtables.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @return DataTable|DataTable\Map Event names for the requested action, or a map when multiple sites/periods are requested.
     */
    public function getNameFromActionId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    /**
     * Returns the Custom Event actions for a specific name subtable.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param int|string $idSubtable Numeric subtable ID, or 'all' to load all subtables.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @return DataTable|DataTable\Map Event actions for the requested name, or a map when multiple sites/periods are requested.
     */
    public function getActionFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    /**
     * Returns the Custom Event categories for a specific name subtable.
     *
     * @param int|string|array<int> $idSite Site ID, a comma-separated list of IDs, an array of IDs, or 'all'.
     * @param string $period Period identifier. Must be one of the enabled period identifiers
     *                       (for example 'day', 'week', 'month', 'year', 'range', or other enabled custom period labels).
     *                       Null is not allowed.
     * @param string $date Date or date-range selector. Accepted forms include a single date/time string parseable by the
     *                     date parser (for example 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'); keywords 'now', 'today',
     *                     'yesterday', 'yesterdaySameTime', 'tomorrow', 'last week', 'last month', 'last year'
     *                     (case-insensitive; space or dash allowed in 'last-week'); multiple-period shortcuts
     *                     'lastN' or 'previousN' (for example 'last7'); or ranges as "start,end" where start is
     *                     'YYYY-MM-DD' or 'last week|last month|last year' and end is 'YYYY-MM-DD' or
     *                     'today|now|yesterday|last week|last month|last year'.
     *                     Timezone: relative keywords are evaluated in the single site's timezone when one site is
     *                     requested; otherwise UTC. Other date strings use the date parser's timezone behavior.
     * @param int|string $idSubtable Numeric subtable ID, or 'all' to load all subtables.
     * @param bool|string $segment Segment definition string, or false for no segment.
     * @return DataTable|DataTable\Map Event categories for the requested name, or a map when multiple sites/periods are requested.
     */
    public function getCategoryFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }
}
