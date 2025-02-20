<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions;

/**
 * Class encapsulating logic to process Day/Period Archiving for the Actions reports
 *
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    public const DOWNLOADS_RECORD_NAME = 'Actions_downloads';
    public const OUTLINKS_RECORD_NAME = 'Actions_outlink';
    public const PAGE_TITLES_RECORD_NAME = 'Actions_actions';
    public const SITE_SEARCH_RECORD_NAME = 'Actions_sitesearch';
    public const SITE_SEARCH_CATEGORY_RECORD_NAME = 'Actions_SiteSearchCategories';
    public const PAGE_URLS_RECORD_NAME = 'Actions_actions_url';

    public const METRIC_PAGEVIEWS_RECORD_NAME = 'Actions_nb_pageviews';
    public const METRIC_UNIQ_PAGEVIEWS_RECORD_NAME = 'Actions_nb_uniq_pageviews';
    public const METRIC_SUM_TIME_RECORD_NAME = 'Actions_sum_time_generation';
    public const METRIC_HITS_TIMED_RECORD_NAME = 'Actions_nb_hits_with_time_generation';
    public const METRIC_DOWNLOADS_RECORD_NAME = 'Actions_nb_downloads';
    public const METRIC_UNIQ_DOWNLOADS_RECORD_NAME = 'Actions_nb_uniq_downloads';
    public const METRIC_OUTLINKS_RECORD_NAME = 'Actions_nb_outlinks';
    public const METRIC_UNIQ_OUTLINKS_RECORD_NAME = 'Actions_nb_uniq_outlinks';
    public const METRIC_SEARCHES_RECORD_NAME = 'Actions_nb_searches';
    public const METRIC_KEYWORDS_RECORD_NAME = 'Actions_nb_keywords';
    public const METRIC_HITS_RECORD_NAME = 'Actions_hits';
}
