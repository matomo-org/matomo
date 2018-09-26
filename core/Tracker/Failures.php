<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Date;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Site;

class Failures
{
    const OPTION_KEY = 'trackingFailures';
    const CLEANUP_OLD_FAILURES_MINUTES = 2880; // 2 days

    const FAILURE_ID_INVALID_SITE = 1;
    const FAILURE_ID_NOT_AUTHENTICATED = 2;

    public function logFailure($idFailure, Request $request)
    {
        $idSite = (int) $request->getIdSite();
        if ($idSite > 9999999 || $idSite < 0) {
            return; // we prevent creating a huge array of sites in option
        }
        $failures = $this->getAllFailures();
        if (!isset($failures[$idSite][$idFailure])) {
            if (!isset($failures[$idSite])) {
                $failures[$idSite] = array();
            }
            $params = $this->getParamsWithTokenAnonymized($request);
            $metadata = array(
                'ts' => Date::now()->getTimestamp(),
                'p' => $params
            );
            $failures[$idSite][$idFailure] = $metadata;
            $this->saveFailures($failures);
        }
    }

    private function getParamsWithTokenAnonymized(Request $request) {
        // eg if there is a typo in the token auth we want to replace it as well to not accidentally leak a token
        // eg imagine a super user tries to issue an API request for a site and sending the wrong parameter for a token...
        // an admin may have view access for this and can see the super users token
        $token = $request->getTokenAuth();
        $params = $request->getRawParams();
        foreach (array('token_auth', 'token', 'tokenauth') as $key) {
            if (isset($params[$key])) {
                $params[$key] = '__ANONYMIZED__';
            }
        }
        foreach ($params as $key => $value) {
            if (!empty($token) && $value === $token) {
                $params[$key] = '__ANONYMIZED__'; // user accidentally posted the token in a wrong field
            } elseif (!empty($value) && is_string($value) && Common::mb_strlen($value) === 32 && ctype_xdigit($value)) {
                $params[$key] = '__ANONYMIZED__'; // user maybe posted a token in a different field... it looks like it might be a token
            }
        }

        return $params;
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

    public function deleteTrackingFailure($idSite, $idFailure)
    {
        $failures = $this->getAllFailures();
        unset($failures[$idSite][$idFailure]);
        $this->saveFailures($failures);
    }

    public function deleteTrackingFailures($idSites)
    {
        $failures = $this->getAllFailures();
        $failures = array_diff_key($failures, array_flip($idSites));
        $this->saveFailures($failures);
    }

    public function makeFailuresHumanReadable($failures)
    {
        $formatted = array();
        foreach ($failures as $idSite => $siteFailures) {
            foreach ($siteFailures as $reason => $siteFailure) {
                $siteFailure['idsite'] = $idSite;
                try {
                    $siteFailure['site_name'] = Site::getNameFor($idSite);
                } catch (UnexpectedWebsiteFoundException $e) {
                    $siteFailure['site_name'] = Piwik::translate('General_Unknown');
                }
                $siteFailure['date_first_occurred'] = Date::factory($siteFailure['ts'])->getLocalized(Date::DATETIME_FORMAT_SHORT);
                $siteFailure['tracking_url'] = http_build_query($siteFailure['p']);
                if (empty($siteFailure['p']['url'])) {
                    $siteFailure['p']['url'] = ' ';// workaround it using the default provider in request constructor
                }
                $request = new Request($siteFailure['p']);
                $siteFailure['url'] = trim($request->getParam('url'));
                $siteFailure['action_name'] = $request->getParam('action_name');
                $siteFailure['id_failure'] = $reason;
                $siteFailure['readable_failure'] = $reason;
                $siteFailure['suggested_solution'] = '';
                $siteFailure['solution_link'] = '';

                switch ($reason) {
                    case self::FAILURE_ID_INVALID_SITE:
                        $siteFailure['readable_failure'] = 'The used idSite does not exist';
                        $siteFailure['solution'] = 'Try to update the configured idSite in the tracker';
                        $siteFailure['solution_url'] = 'https://matomo.org';
                        break;
                    default:
                        Piwik::postEvent('Tracking.Failure.makeFailureHumanReadable', array(&$siteFailure));

                        break;
                }
                unset($siteFailure['p']);
                $formatted[] = $siteFailure;
            }

        }

        return $formatted;
    }
}
