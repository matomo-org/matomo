<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Exception;
use Piwik\Access;
use Piwik\Common;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Manager;
use Piwik\Tracker;

/**
 * An action
 *
 */
class Failures
{
    const OPTION_KEY = 'trackingFailures';
    const FAILURE_INVALID_SITE = 'invsite';
    const CLEANUP_OLD_FAILURES_MINUTES = 2880; // 2 days

    public function logFailure($idSite, $reason, $metadata)
    {
        $idSite = (int) $idSite;
        if ($idSite > 9999999 || $idSite < 0) {
            return; // we prevent creating a huge array of sites in option
        }
        $failures = $this->getAllFailures();
        if (!isset($failures[$idSite][$reason])) {
            if (!isset($failures[$idSite])) {
                $failures[$idSite] = array();
            }
            if (empty($metadata)) {
                $metadata = array();
            }
            $metadata['ts'] = Date::now()->getTimestamp();
            $failures[$idSite][$reason] = $metadata;
            $this->saveFailures($failures);
        }
    }

    public function removeFailuresOlderThanMinutes($minutes)
    {
        $tsMinutesAgo = Date::now()->subPeriod($minutes, 'minute')->getTimestamp();

        $failures = $this->getAllFailures();
        foreach ($failures as $idSite => $siteFailures) {
            foreach ($siteFailures as $reason => $meta) {
                if (empty($meta['ts']) || $tsMinutesAgo > $meta['ts']) {
                    unset($failures[$idSite][$reason]);
                }
            }
            if (empty($failures[$idSite])) {
                unset($failures[$idSite]);
            }
        }

        $this->saveFailures($failures);
    }

    public function saveFailures($failures)
    {
        // we do not autoload as it won't be needed in most cases and the entry could be "big" potentially
        Option::set(self::OPTION_KEY, json_encode($failures), $autoload = 0);
    }

    public function getAllFailures()
    {
        $failures = json_decode(Option::get(self::OPTION_KEY), true);
        if (empty($failures)) {
            $failures = array();
        }
        return $failures;
    }

    public function getAllFailuresDependingOnPermissions()
    {
        $failures = $this->getAllFailures();

        if (!Piwik::hasUserSuperUserAccess()) {
            $idSites = Access::getInstance()->getSitesIdWithAdminAccess();
            $failures = array_intersect_key($failures, array_flip($idSites));
        }

        return $failures;
    }
}
