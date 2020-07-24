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
use Matomo\Network\IP;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Anonymize visitor IP addresses to comply with the privacy laws/guidelines in countries, such as Germany.
 */
class ReferrerAnonymizer
{
    const EXCLUDE_QUERY = 'exclude_query';
    const EXCLUDE_PATH = 'exclude_path';
    const EXCLUDE_SUBDOMAIN = 'exclude_subdomain';
    const EXCLUDE_ALL = 'exclude_all';
    const EXCLUDE_NONE = '';

    public static function getAvailableAnonymizationOptions()
    {
        return array(
            self::EXCLUDE_NONE => 'Don\'t anonymize',
            self::EXCLUDE_QUERY => 'Remove query parameters',
            self::EXCLUDE_PATH => 'Keep only the domain, remove path and query parameters',
            self::EXCLUDE_SUBDOMAIN => 'Keep only top domain, remove any subdomain, path and query parameters',
            self::EXCLUDE_ALL => 'Only record the type of referrer, not the actual referrer'
        );
    }

    public static function anonymiseReferrer(VisitProperties $visitProperties)
    {
        $privacyConfig = new PrivacyManagerConfig();

        switch ($privacyConfig->anonymizeReferrer) {
            case self::EXCLUDE_QUERY:
                break;
            case self::EXCLUDE_PATH:
                break;
            case self::EXCLUDE_SUBDOMAIN:
                break;
            case self::EXCLUDE_ALL:
                break;
        }
    }

}
