<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers;

class Archiver extends \Piwik\Plugin\Archiver
{
    const SEARCH_ENGINES_RECORD_NAME = 'Referrers_keywordBySearchEngine';
    const SOCIAL_NETWORKS_RECORD_NAME = 'Referrers_urlBySocialNetwork';
    const KEYWORDS_RECORD_NAME = 'Referrers_searchEngineByKeyword';
    const CAMPAIGNS_RECORD_NAME = 'Referrers_keywordByCampaign';
    const WEBSITES_RECORD_NAME = 'Referrers_urlByWebsite';
    const REFERRER_TYPE_RECORD_NAME = 'Referrers_type';
    const METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME = 'Referrers_distinctSearchEngines';
    const METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME = 'Referrers_distinctSocialNetworks';
    const METRIC_DISTINCT_KEYWORD_RECORD_NAME = 'Referrers_distinctKeywords';
    const METRIC_DISTINCT_CAMPAIGN_RECORD_NAME = 'Referrers_distinctCampaigns';
    const METRIC_DISTINCT_WEBSITE_RECORD_NAME = 'Referrers_distinctWebsites';
    const METRIC_DISTINCT_URLS_RECORD_NAME = 'Referrers_distinctWebsitesUrls';
}
