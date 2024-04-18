<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

class Archiver extends \Piwik\Plugin\Archiver
{
    public const SEARCH_ENGINES_RECORD_NAME = 'Referrers_keywordBySearchEngine';
    public const SOCIAL_NETWORKS_RECORD_NAME = 'Referrers_urlBySocialNetwork';
    public const KEYWORDS_RECORD_NAME = 'Referrers_searchEngineByKeyword';
    public const CAMPAIGNS_RECORD_NAME = 'Referrers_keywordByCampaign';
    public const WEBSITES_RECORD_NAME = 'Referrers_urlByWebsite';
    public const REFERRER_TYPE_RECORD_NAME = 'Referrers_type';
    public const METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME = 'Referrers_distinctSearchEngines';
    public const METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME = 'Referrers_distinctSocialNetworks';
    public const METRIC_DISTINCT_KEYWORD_RECORD_NAME = 'Referrers_distinctKeywords';
    public const METRIC_DISTINCT_CAMPAIGN_RECORD_NAME = 'Referrers_distinctCampaigns';
    public const METRIC_DISTINCT_WEBSITE_RECORD_NAME = 'Referrers_distinctWebsites';
    public const METRIC_DISTINCT_URLS_RECORD_NAME = 'Referrers_distinctWebsitesUrls';
}
