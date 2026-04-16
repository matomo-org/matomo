<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Overlay;

use Exception;
use Piwik\API\Request;
use Piwik\Config\GeneralConfig;
use Piwik\DataTable;
use Piwik\Plugins\Transitions\API as APITransitions;
use Piwik\Tracker\PageUrl;

/**
 * The Overlay API exposes translation data and overlay-specific page transition reports.
 *
 * @method static \Piwik\Plugins\Overlay\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the translation strings used by the Overlay client.
     *
     * @return array<string, string> Overlay translation strings keyed by client-side identifier.
     */
    public function getTranslations()
    {
        $translations = array(
            'oneClick'         => 'Overlay_OneClick',
            'clicks'           => 'Overlay_Clicks',
            'clicksFromXLinks' => 'Overlay_ClicksFromXLinks',
            'link'             => 'Overlay_Link',
        );

        return array_map(array('\\Piwik\\Piwik','translate'), $translations);
    }

    /**
     * Returns the excluded query parameters configured for a website.
     * This information is used for client-side URL normalization.
     *
     * @param int $idSite Deprecated site ID parameter retained for backward compatibility.
     * @return array Excluded query parameter names returned by `SitesManager.getExcludedQueryParameters`.
     * @deprecated use SitesManager.getExcludedQueryParameters instead
     * @todo Remove in Matomo 6
     */
    public function getExcludedQueryParameters(int $idSite)
    {
        return Request::processRequest('SitesManager.getExcludedQueryParameters');
    }

    /**
     * Returns the following pages reached after visits to a specific page URL.
     * This is done on the logs, not the archives.
     *
     * Note: if you use this method via the regular API, the number of results will be limited.
     * Make sure, you set filter_limit=-1 in the request.
     *
     * @param string $url Page URL to analyze.
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable Rows for following pages, outlinks, and downloads reached from the requested URL.
     */
    public function getFollowingPages($url, int $idSite, $period, $date, $segment = false)
    {
        $url = PageUrl::excludeQueryParametersFromUrl($url, $idSite);
        // we don't unsanitize $url here. it will be done in the Transitions plugin.

        $resultDataTable = new DataTable();

        try {
            $limitBeforeGrouping = (int)GeneralConfig::getConfigValue('overlay_following_pages_limit');
            $transitionsReport = APITransitions::getInstance()->getTransitionsForAction(
                $url,
                $type = 'url',
                $idSite,
                $period,
                $date,
                $segment,
                $limitBeforeGrouping,
                $part = 'followingActions'
            );
        } catch (Exception $e) {
            return $resultDataTable;
        }

        $reports = array('followingPages', 'outlinks', 'downloads');
        foreach ($reports as $reportName) {
            if (!isset($transitionsReport[$reportName])) {
                continue;
            }
            foreach ($transitionsReport[$reportName]->getRows() as $row) {
                // don't touch the row at all for performance reasons
                $resultDataTable->addRow($row);
            }
        }

        return $resultDataTable;
    }
}
