<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics\Formatter;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tracker;
use Psr\Log\LoggerInterface;

/**
 * @see plugins/Live/Visitor.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Live/Visitor.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 * The Live! API lets you access complete visit level information about your visitors. Combined with the power of <a href='http://piwik.org/docs/analytics-api/segmentation/' target='_blank'>Segmentation</a>,
 * you will be able to request visits filtered by any criteria.
 *
 * The method "getLastVisitsDetails" will return extensive data for each visit, which includes: server time, visitId, visitorId,
 * visitorType (new or returning), number of pages, list of all pages (and events, file downloaded and outlinks clicked),
 * custom variables names and values set to this visit, number of goal conversions (and list of all Goal conversions for this visit,
 * with time of conversion, revenue, URL, etc.), but also other attributes such as: days since last visit, days since first visit,
 * country, continent, visitor IP,
 * provider, referrer used (referrer name, keyword if it was a search engine, full URL), campaign name and keyword, operating system,
 * browser, type of screen, resolution, supported browser plugins (flash, java, silverlight, pdf, etc.), various dates & times format to make
 * it easier for API users... and more!
 *
 * With the parameter <a href='http://piwik.org/docs/analytics-api/segmentation/' rel='noreferrer' target='_blank'>'&segment='</a> you can filter the
 * returned visits by any criteria (visitor IP, visitor ID, country, keyword used, time of day, etc.).
 *
 * The method "getCounters" is used to return a simple counter: visits, number of actions, number of converted visits, in the last N minutes.
 *
 * See also the documentation about <a href='http://piwik.org/docs/real-time/' rel='noreferrer' target='_blank'>Real time widget and visitor level reports</a> in Piwik.
 * @method static \Piwik\Plugins\Live\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const VISITOR_PROFILE_MAX_VISITS_TO_AGGREGATE = 100;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * This will return simple counters, for a given website ID, for visits over the last N minutes
     *
     * @param int $idSite Id Site
     * @param int $lastMinutes Number of minutes to look back at
     * @param bool|string $segment
     * @param array $showColumns The columns to show / not to request. Eg 'visits', 'actions', ...
     * @param array $hideColumns The columns to hide / not to request. Eg 'visits', 'actions', ...
     * @return array( visits => N, actions => M, visitsConverted => P )
     */
    public function getCounters($idSite, $lastMinutes, $segment = false, $showColumns = array(), $hideColumns = array())
    {
        Piwik::checkUserHasViewAccess($idSite);
        $model = new Model();

        $counters = array();

        $hasVisits = true;
        if ($this->shouldColumnBePresentInResponse('visits', $showColumns, $hideColumns)) {
            $counters['visits'] = $model->getNumVisits($idSite, $lastMinutes, $segment);
            $hasVisits = !empty($counters['visits']);
        }

        if ($this->shouldColumnBePresentInResponse('actions', $showColumns, $hideColumns)) {
            if ($hasVisits) {
                $counters['actions'] = $model->getNumActions($idSite, $lastMinutes, $segment);
            } else {
                $counters['actions'] = 0;
            }
        }

        if ($this->shouldColumnBePresentInResponse('visitors', $showColumns, $hideColumns)) {
            if ($hasVisits) {
                $counters['visitors'] = $model->getNumVisitors($idSite, $lastMinutes, $segment);
            } else {
                $counters['visitors'] = 0;
            }
        }

        if ($this->shouldColumnBePresentInResponse('visitsConverted', $showColumns, $hideColumns)) {
            if ($hasVisits) {
                $counters['visitsConverted'] = $model->getNumVisitsConverted($idSite, $lastMinutes, $segment);
            } else {
                $counters['visitsConverted'] = 0;
            }
        }

        return array($counters);
    }

    private function shouldColumnBePresentInResponse($column, $showColumns, $hideColumns)
    {
        $show = (empty($showColumns) || in_array($column, $showColumns));
        $hide = in_array($column, $hideColumns);

        return $show && !$hide;
    }

    /**
     * The same functionality can be obtained using segment=visitorId==$visitorId with getLastVisitsDetails
     *
     * @deprecated
     * @ignore
     * @param int $visitorId
     * @param int $idSite
     * @param int $filter_limit
     * @param bool $flat Whether to flatten the visitor details array
     *
     * @return DataTable
     */
    public function getLastVisitsForVisitor($visitorId, $idSite, $filter_limit = 10, $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $table = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $segment = false, $offset = 0, $filter_limit, $minTimestamp = false, $filterSortOrder = false, $visitorId);
        $this->addFilterToCleanVisitors($table, $idSite, $flat);

        return $table;
    }

    /**
     * Returns the last visits tracked in the specified website
     * You can define any number of filters: none, one, many or all parameters can be defined
     *
     * @param int $idSite Site ID
     * @param bool|string $period Period to restrict to when looking at the logs
     * @param bool|string $date Date to restrict to
     * @param bool|int $segment (optional) Number of visits rows to return
     * @param bool|int $countVisitorsToFetch DEPRECATED (optional) Only return the last X visits. Please use the API paramaeter 'filter_offset' and 'filter_limit' instead.
     * @param bool|int $minTimestamp (optional) Minimum timestamp to restrict the query to (useful when paginating or refreshing visits)
     * @param bool $flat
     * @param bool $doNotFetchActions
     * @return DataTable
     */
    public function getLastVisitsDetails($idSite, $period = false, $date = false, $segment = false, $countVisitorsToFetch = false, $minTimestamp = false, $flat = false, $doNotFetchActions = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        if ($countVisitorsToFetch !== false) {
            $filterLimit     = (int) $countVisitorsToFetch;
            $filterOffset    = 0;
        } else {
            $filterLimit     = Common::getRequestVar('filter_limit', 10, 'int');
            $filterOffset    = Common::getRequestVar('filter_offset', 0, 'int');
        }

        $filterSortOrder = Common::getRequestVar('filter_sort_order', false, 'string');

        $dataTable = $this->loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $segment, $filterOffset, $filterLimit, $minTimestamp, $filterSortOrder, $visitorId = false);
        $this->addFilterToCleanVisitors($dataTable, $idSite, $flat, $doNotFetchActions);

        $filterSortColumn = Common::getRequestVar('filter_sort_column', false, 'string');

        if ($filterSortColumn) {
            $this->logger->warning('Sorting the API method "Live.getLastVisitDetails" by column is currently not supported. To avoid this warning remove the URL parameter "filter_sort_column" from your API request.');
        }

        // Usually one would Sort a DataTable and then apply a Limit. In this case we apply a Limit first in SQL
        // for fast offset usage see https://github.com/piwik/piwik/issues/7458. Sorting afterwards would lead to a
        // wrong sorting result as it would only sort the limited results. Therefore we do not support a Sort for this
        // API
        $dataTable->disableFilter('Sort');
        $dataTable->disableFilter('Limit'); // limit is already applied here

        return $dataTable;
    }

    /**
     * Returns an array describing a visitor using her last visits (uses a maximum of 100).
     *
     * @param int $idSite Site ID
     * @param bool|false|string $visitorId The ID of the visitor whose profile to retrieve.
     * @param bool|false|string $segment
     * @return array
     */
    public function getVisitorProfile($idSite, $visitorId = false, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        if ($visitorId === false) {
            $visitorId = $this->getMostRecentVisitorId($idSite, $segment);
        }

        $newSegment = ($segment === false ? '' : $segment . ';') . 'visitorId==' . $visitorId;

        $visits = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $newSegment,
            $offset = 0,
            $limit = self::VISITOR_PROFILE_MAX_VISITS_TO_AGGREGATE);
        $this->addFilterToCleanVisitors($visits, $idSite, $flat = false, $doNotFetchActions = false, $filterNow = true);

        if ($visits->getRowsCount() == 0) {
            return array();
        }

        $profile = new VisitorProfile($idSite);
        $result = $profile->makeVisitorProfile($visits, $visitorId, $segment);

        /**
         * Triggered in the Live.getVisitorProfile API method. Plugins can use this event
         * to discover and add extra data to visitor profiles.
         *
         * For example, if an email address is found in a custom variable, a plugin could load the
         * gravatar for the email and add it to the visitor profile, causing it to display in the
         * visitor profile popup.
         *
         * The following visitor profile elements can be set to augment the visitor profile popup:
         *
         * - **visitorAvatar**: A URL to an image to display in the top left corner of the popup.
         * - **visitorDescription**: Text to be used as the tooltip of the avatar image.
         *
         * @param array &$visitorProfile The unaugmented visitor profile info.
         */
        Piwik::postEvent('Live.getExtraVisitorDetails', array(&$result));

        return $result;
    }

    /**
     * Returns the visitor ID of the most recent visit.
     *
     * @param int $idSite
     * @param bool|string $segment
     * @return string
     */
    public function getMostRecentVisitorId($idSite, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->loadLastVisitorDetailsFromDatabase(
            $idSite, $period = false, $date = false, $segment, $offset = 0, $limit = 1
        );

        if (0 >= $dataTable->getRowsCount()) {
            return false;
        }

        $visitorFactory = new VisitorFactory();
        $visitDetails   = $dataTable->getFirstRow()->getColumns();
        $visitor        = $visitorFactory->create($visitDetails);

        return $visitor->getVisitorId();
    }

    /**
     * @deprecated
     */
    public function getLastVisits($idSite, $filter_limit = 10, $minTimestamp = false)
    {
        return $this->getLastVisitsDetails($idSite, $period = false, $date = false, $segment = false, $filter_limit, $minTimestamp, $flat = false);
    }

    /**
     * For an array of visits, query the list of pages for this visit
     * as well as make the data human readable
     * @param DataTable $dataTable
     * @param int $idSite
     * @param bool $flat whether to flatten the array (eg. 'customVariables' names/values will appear in the root array rather than in 'customVariables' key
     * @param bool $doNotFetchActions If set to true, we only fetch visit info and not actions (much faster)
     * @param bool $filterNow If true, the visitors will be cleaned immediately
     */
    private function addFilterToCleanVisitors(DataTable $dataTable, $idSite, $flat = false, $doNotFetchActions = false, $filterNow = false)
    {
        $filter = 'queueFilter';
        if ($filterNow) {
            $filter = 'filter';
        }

        $dataTable->$filter(function ($table) use ($idSite, $flat, $doNotFetchActions) {
            /** @var DataTable $table */
            $actionsLimit = (int)Config::getInstance()->General['visitor_log_maximum_actions_per_visit'];

            $visitorFactory = new VisitorFactory();
            $website        = new Site($idSite);
            $timezone       = $website->getTimezone();
            $currency       = $website->getCurrency();
            $currencies     = APISitesManager::getInstance()->getCurrencySymbols();

            // live api is not summable, prevents errors like "Unexpected ECommerce status value"
            $table->deleteRow(DataTable::ID_SUMMARY_ROW);

            foreach ($table->getRows() as $visitorDetailRow) {
                $visitorDetailsArray = Visitor::cleanVisitorDetails($visitorDetailRow->getColumns());

                $visitor = $visitorFactory->create($visitorDetailsArray);
                $visitorDetailsArray = $visitor->getAllVisitorDetails();

                $visitorDetailsArray['siteCurrency'] = $currency;
                $visitorDetailsArray['siteCurrencySymbol'] = @$currencies[$visitorDetailsArray['siteCurrency']];
                $visitorDetailsArray['serverTimestamp'] = $visitorDetailsArray['lastActionTimestamp'];

                $dateTimeVisit = Date::factory($visitorDetailsArray['lastActionTimestamp'], $timezone);
                if ($dateTimeVisit) {
                    $visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized(Date::TIME_FORMAT);
                    $visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized(Date::DATE_FORMAT_LONG);
                }

                $dateTimeVisitFirstAction = Date::factory($visitorDetailsArray['firstActionTimestamp'], $timezone);
                $visitorDetailsArray['serverDatePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized(Date::DATE_FORMAT_LONG);
                $visitorDetailsArray['serverTimePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized(Date::TIME_FORMAT);

                $visitorDetailsArray['actionDetails'] = array();
                if (!$doNotFetchActions) {
                    $visitorDetailsArray = Visitor::enrichVisitorArrayWithActions($visitorDetailsArray, $actionsLimit, $timezone);
                }

                if ($flat) {
                    $visitorDetailsArray = Visitor::flattenVisitorDetailsArray($visitorDetailsArray);
                }

                $visitorDetailRow->setColumns($visitorDetailsArray);
            }
        });
    }

    private function loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $segment = false, $offset = 0, $limit = 100, $minTimestamp = false, $filterSortOrder = false, $visitorId = false)
    {
        $model = new Model();
        $data = $model->queryLogVisits($idSite, $period, $date, $segment, $offset, $limit, $visitorId, $minTimestamp, $filterSortOrder);
        return $this->makeVisitorTableFromArray($data);
    }

    /**
     * @param $data
     * @return DataTable
     * @throws Exception
     */
    private function makeVisitorTableFromArray($data)
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray($data);

        if (!empty($data[0])) {
            $columnsToNotAggregate = array_map(function () {
                return 'skip';
            }, $data[0]);

            $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsToNotAggregate);
        }

        return $dataTable;
    }


}
