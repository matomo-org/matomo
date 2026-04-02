<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution;

use Exception;
use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Site;

/**
 * @see plugins/Resolution/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Resolution/functions.php';

/**
 * Provides API methods for screen resolution and device configuration reports.
 *
 * @method static \Piwik\Plugins\Resolution\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @param int|string|int[] $idSite
     * @param string|null|false $segment
     * @return \Piwik\DataTable|\Piwik\DataTable\Map
     */
    protected function getDataTable(string $name, $idSite, string $period, string $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    /**
     * Returns visitor screen resolution metrics for the requested site and period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Screen resolution metrics for the requested site and period.
     */
    public function getResolution($idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $idSites = Site::getIdSitesFromIdSitesString($idSite);

        // filter sites where model report disabled by compliance
        $idSitesFiltered = array_filter($idSites, function ($idSite) {
            return !Resolution::isScreenResolutionDetectionDisabledByCompliancePolicy((int)$idSite);
        });

        // show an error only if none of the requested sites is left
        if (count($idSitesFiltered) === 0 && count($idSites) !== count($idSitesFiltered)) {
            throw new Exception(Piwik::translate('Resolution_ScreenResolutionReportDisabledByCompliancePolicy'));
        }

        $dataTable = $this->getDataTable(Archiver::RESOLUTION_RECORD_NAME, $idSitesFiltered, $period, $date, $segment);
        $dataTable->filter('AddSegmentValue');
        return $dataTable;
    }

    /**
     * Returns visitor device configuration metrics for the requested site and period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Device configuration metrics for the requested site and period.
     */
    public function getConfiguration($idSite, string $period, string $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CONFIGURATION_RECORD_NAME, $idSite, $period, $date, $segment);
        // use GroupBy filter to avoid duplicate rows if old reports are displayed
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\getConfigurationLabel'));
        return $dataTable;
    }
}
