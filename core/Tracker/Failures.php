<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Date;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Db;

class Failures
{
    const OPTION_KEY = 'trackingFailures';
    const CLEANUP_OLD_FAILURES_DAYS = 2;

    const FAILURE_ID_INVALID_SITE = 1;
    const FAILURE_ID_NOT_AUTHENTICATED = 2;

    private $table = 'tracking_failure';
    private $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
    }

    public function logFailure($idFailure, Request $request)
    {
        $idSite = (int) $request->getIdSite();
        $idFailure = (int) $idFailure;

        if ($idSite > 9999999 || $idSite < 0 || $this->hasLoggedFailure($idSite, $idFailure)) {
            return; // we prevent creating huge amount of entries in the cache
        }

        $params = $this->getParamsWithTokenAnonymized($request);
        $sql = sprintf('INSERT INTO %s (`idsite`, `idfailure`, `date_first_occurred`, `request_url`) VALUES(?,?,?,?)', $this->tablePrefixed);

        Db::get()->query($sql, array($idSite, $idFailure, Date::now()->getDatetime(), http_build_query($params)));
    }

    public function hasLoggedFailure($idSite, $idFailure)
    {
        return false;
    }

    private function getParamsWithTokenAnonymized(Request $request)
    {
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

    public function removeFailuresOlderThanDays($days)
    {
        $minutesAgo = Date::now()->subDay($days)->getDatetime();

        Db::query(sprintf('DELETE FROM %s WHERE date_first_occurred >= ?', $this->tablePrefixed), array($minutesAgo));
    }

    public function getAllFailures()
    {
        return Db::fetchAll(sprintf('SELECT * FROM %s', $this->tablePrefixed));
    }

    public function deleteTrackingFailure($idSite, $idFailure)
    {
        Db::query(sprintf('DELETE FROM %s WHERE idsite = ? and idfailure = ?', $this->tablePrefixed), array($idSite, $idFailure));
    }

    public function deleteTrackingFailures($idSites)
    {
        if (!empty($idSites)) {
            $idSites = array_map('intval', $idSites);
            $idSites = implode(',', $idSites);
            Db::query(sprintf('DELETE FROM %s WHERE idsite = IN(%s)', $this->tablePrefixed, $idSites));
        }
    }

    public function deleteAllTrackingFailures()
    {
        Db::query(sprintf('DELETE FROM %s', $this->tablePrefixed));
    }

    public function makeFailuresHumanReadable($failures)
    {
        foreach ($failures as $siteFailure) {
            try {
                $siteFailure['site_name'] = Site::getNameFor($siteFailure['idsite']);
            } catch (UnexpectedWebsiteFoundException $e) {
                $siteFailure['site_name'] = Piwik::translate('General_Unknown');
            }
            $siteFailure['pretty_date_first_occurred'] = Date::factory($siteFailure['ts'])->getLocalized(Date::DATETIME_FORMAT_SHORT);
            parse_str($siteFailure['request_url'], $params);
            if (empty($params['url'])) {
                $params['url'] = ' ';// workaround it using the default provider in request constructor
            }
            $request = new Request($params);
            $siteFailure['url'] = trim($request->getParam('url'));
            $siteFailure['action_name'] = $request->getParam('action_name');
            $siteFailure['pretty_failure'] = '';
            $siteFailure['suggested_solution'] = '';
            $siteFailure['solution_link'] = '';
            unset($siteFailure['request_url']);

            switch ($siteFailure['idfailure']) {
                case self::FAILURE_ID_INVALID_SITE:
                    $siteFailure['readable_failure'] = 'The used idSite does not exist';
                    $siteFailure['solution'] = 'Try to update the configured idSite in the tracker';
                    $siteFailure['solution_url'] = 'https://matomo.org';
                    break;
                case self::FAILURE_ID_NOT_AUTHENTICATED:
                    $siteFailure['readable_failure'] = 'The used idSite does not exist';
                    $siteFailure['solution'] = 'Try to update the configured idSite in the tracker';
                    $siteFailure['solution_url'] = 'https://matomo.org';
                    break;
            }
        }

        Piwik::postEvent('Tracking.makeFailuresHumanReadable', array(&$failures));

        return $failures;
    }
}
