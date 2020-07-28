<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;

/**
 * Anonymize visitor IP addresses to comply with the privacy laws/guidelines in countries, such as Germany.
 */
class ReferrerAnonymizer
{
    const EXCLUDE_QUERY = 'exclude_query';
    const EXCLUDE_PATH = 'exclude_path';
    const EXCLUDE_ALL = 'exclude_all';
    const EXCLUDE_URL = 'exclude_url';
    const EXCLUDE_NONE = '';

    public function getAvailableAnonymizationOptions()
    {
        // referer_url full referrer url
        // referer_type: the type of referrer
        // referer_name: eg referer host of website or campaign name or search engine name or social network name
        // referer_keyword: searched keyword or campaign keyword
        return array(
            self::EXCLUDE_NONE => 'Don\'t anonymize the referrer',
            self::EXCLUDE_QUERY => 'Remove URL query parameters from the referrer URL',
            self::EXCLUDE_PATH => 'Keep only the domain from the referrer URL, remove path and query parameters',
            self::EXCLUDE_URL => 'Don\'t record the referrer url but still detect referrer type, keywords, and campaign names',
            self::EXCLUDE_ALL => "Don't record any kind of referrer, not even the type of referrer."
        );
    }

    public function anonymiseReferrerUrl($url, $anonymizeOption)
    {
        if (empty($url)) {
            return $url;
        }

        switch ($anonymizeOption) {
            case self::EXCLUDE_QUERY:
                $url = strtok($url, '?');
                break;
            case self::EXCLUDE_PATH:
                $urlParts = @parse_url($url);
                if (!empty($urlParts['host']) && !empty($urlParts['path'])) {
                    $scheme = $urlParts['scheme'] ?? '';
                    $url =  $scheme . '://' . $urlParts['host'] . '/';
                }
                break;
            case self::EXCLUDE_URL:
            case self::EXCLUDE_ALL:
                $url = '';
                break;
        }

        return $url;
    }

}
