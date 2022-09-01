<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Piwik;

class ReferrerAnonymizer
{
    const EXCLUDE_QUERY = 'exclude_query';
    const EXCLUDE_PATH = 'exclude_path';
    const EXCLUDE_ALL = 'exclude_all';
    const EXCLUDE_NONE = '';

    public function getAvailableAnonymizationOptions()
    {
        return array(
            self::EXCLUDE_NONE => Piwik::translate('PrivacyManager_AnonymizeReferrerExcludeNone'),
            self::EXCLUDE_QUERY => Piwik::translate('PrivacyManager_AnonymizeReferrerExcludeQuery'),
            self::EXCLUDE_PATH => Piwik::translate('PrivacyManager_AnonymizeReferrerExcludePath'),
            self::EXCLUDE_ALL => Piwik::translate('PrivacyManager_AnonymizeReferrerExcludeAll')
            // but try to track the type still
        );
    }

    // referer_keyword: searched keyword or campaign keyword
    public function anonymiseReferrerKeyword($keyword, $referrerType, $anonymizeOption)
    {
        if ($anonymizeOption == self::EXCLUDE_NONE) {
            return $keyword; // default, nothing to anonymise
        }

        if ($referrerType == Common::REFERRER_TYPE_CAMPAIGN) {
            return $keyword; // we always want to keep the keyword since it is from the viewed page url, not the referrer
        }

        if ($anonymizeOption == self::EXCLUDE_ALL) {
            return '';
        }

        if (in_array($anonymizeOption, [self::EXCLUDE_QUERY, self::EXCLUDE_PATH])) {
            return ''; // the keyword should have not been detected
        }
        return $keyword;
    }

    // referer_name: eg referer host of website or campaign name or search engine name or social network name
    public function anonymiseReferrerName($name, $referrerType, $anonymizeOption)
    {
        if ($anonymizeOption == self::EXCLUDE_NONE) {
            return $name; // default, nothing to anonymise
        }

        if ($referrerType == Common::REFERRER_TYPE_CAMPAIGN) {
            return $name; // we always want to keep the keyword since it is from the viewed page url, not the referrer
        }

        if ($referrerType == Common::REFERRER_TYPE_SOCIAL_NETWORK || $referrerType == Common::REFERRER_TYPE_SEARCH_ENGINE) {
            return $name; // we also keep the name of the social network or search engine since it should not be personal information
        }

        if ($anonymizeOption == self::EXCLUDE_ALL) {
            // mostly for website referrers we unset it
            return '';
        }

        // I don't think it should be anonymised further because it would only store the hostname anyway...
        // so if website is being used and no matter if query or path should be anonymised it should be fine to keep the value
        return $name;
    }

    public function anonymiseReferrerUrl($url, $anonymizeOption)
    {
        if ($anonymizeOption === self::EXCLUDE_NONE) {
            return $url;
        }

        if (!is_string($url) && !is_numeric($url)) {
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
                    if ($scheme) {
                        $scheme .= '://';
                    } elseif (strpos($url, '//') === 0) {
                        $scheme = '//';
                    }
                    $url =  $scheme . $urlParts['host'] . '/';
                }
                break;
            case self::EXCLUDE_ALL:
                $url = '';
                break;
        }

        return $url;
    }

}
