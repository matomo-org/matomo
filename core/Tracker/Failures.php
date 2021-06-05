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
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Db as PiwikDb;

class Failures
{
    const CLEANUP_OLD_FAILURES_DAYS = 2;
    const FAILURE_ID_INVALID_SITE = 1;
    const FAILURE_ID_NOT_AUTHENTICATED = 2;

    private $table = 'tracking_failure';
    private $tablePrefixed;
    private $now;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
    }

    public function setNow(Date $now)
    {
        $this->now = $now;
    }

    private function getNow()
    {
        if (isset($this->now)) {
            return $this->now;
        }
        return Date::now();
    }

    public function logFailure($idFailure, Request $request)
    {
        $isVisitExcluded = $request->getMetadata('CoreHome', 'isVisitExcluded');

        if ($isVisitExcluded === null) {
            try {
                $visitExcluded = new VisitExcluded($request);
                $isVisitExcluded = $visitExcluded->isExcluded();
            } catch (InvalidRequestParameterException $e) {
                // we ignore this error and assume visit is not excluded... happens eg when using `cip` and request was
                // not authenticated...
                $isVisitExcluded = false;
            }
        }

        if ($isVisitExcluded) {
            return;
        }

        $idSite = (int) $request->getIdSiteUnverified();
        $idFailure = (int) $idFailure;

        if ($idSite > 9999999 || $idSite < 0 || $this->hasLoggedFailure($idSite, $idFailure)) {
            return; // we prevent creating huge amount of entries in the cache
        }

        $params = $this->getParamsWithTokenAnonymized($request);
        $sql = sprintf('INSERT INTO %s (`idsite`, `idfailure`, `date_first_occurred`, `request_url`) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE idsite=idsite;', $this->tablePrefixed);

        PiwikDb::get()->query($sql, array($idSite, $idFailure, $this->getNow()->getDatetime(), http_build_query($params)));
    }

    private function hasLoggedFailure($idSite, $idFailure)
    {
        $sql = sprintf('SELECT idsite FROM %s WHERE idsite = ? and idfailure = ?', $this->tablePrefixed);
        $row = PiwikDb::fetchRow($sql, array($idSite, $idFailure));

        return !empty($row);
    }

    private function getParamsWithTokenAnonymized(Request $request)
    {
        // eg if there is a typo in the token auth we want to replace it as well to not accidentally leak a token
        // eg imagine a super user tries to issue an API request for a site and sending the wrong parameter for a token...
        // an admin may have view access for this and can see the super users token
        $token = $request->getTokenAuth();
        $params = $request->getRawParams();
        foreach (array('token_auth', 'token', 'tokenauth', 'token__auth') as $key) {
            if (isset($params[$key])) {
                $params[$key] = '__TOKEN_AUTH__';
            }
        }
        foreach ($params as $key => $value) {
            if (!empty($token) && $value === $token) {
                $params[$key] = '__TOKEN_AUTH__'; // user accidentally posted the token in a wrong field
            } elseif (!empty($value) && is_string($value)
                && mb_strlen($value) >= 29 && mb_strlen($value) <= 36
                && ctype_xdigit($value)) {
                $params[$key] = '__TOKEN_AUTH__'; // user maybe posted a token in a different field... it looks like it might be a token
            }
        }

        return $params;
    }

    public function removeFailuresOlderThanDays($days)
    {
        $minutesAgo = $this->getNow()->subDay($days)->getDatetime();

        PiwikDb::query(sprintf('DELETE FROM %s WHERE date_first_occurred < ?', $this->tablePrefixed), array($minutesAgo));
    }

    public function getAllFailures()
    {
        $failures = PiwikDb::fetchAll(sprintf('SELECT * FROM %s', $this->tablePrefixed));
        return $this->enrichFailures($failures);
    }

    public function getFailuresForSites($idSites)
    {
        if (empty($idSites)) {
            return array();
        }
        $idSites = array_map('intval', $idSites);
        $idSites = implode(',', $idSites);
        $failures = PiwikDb::fetchAll(sprintf('SELECT * FROM %s WHERE idsite IN (%s)', $this->tablePrefixed, $idSites));
        return $this->enrichFailures($failures);
    }

    public function deleteTrackingFailure($idSite, $idFailure)
    {
        PiwikDb::query(sprintf('DELETE FROM %s WHERE idsite = ? and idfailure = ?', $this->tablePrefixed), array($idSite, $idFailure));
    }

    public function deleteTrackingFailures($idSites)
    {
        if (!empty($idSites)) {
            $idSites = array_map('intval', $idSites);
            $idSites = implode(',', $idSites);
            PiwikDb::query(sprintf('DELETE FROM %s WHERE idsite IN(%s)', $this->tablePrefixed, $idSites));
        }
    }

    public function deleteAllTrackingFailures()
    {
        PiwikDb::query(sprintf('DELETE FROM %s', $this->tablePrefixed));
    }

    private function enrichFailures($failures)
    {
        foreach ($failures as &$failure) {
            try {
                $failure['site_name'] = Site::getNameFor($failure['idsite']);
            } catch (UnexpectedWebsiteFoundException $e) {
                $failure['site_name'] = Piwik::translate('General_Unknown');
            }
            $failure['pretty_date_first_occurred'] = Date::factory($failure['date_first_occurred'])->getLocalized(Date::DATETIME_FORMAT_SHORT);
            parse_str($failure['request_url'], $params);
            if (empty($params['url'])) {
                $params['url'] = ' ';// workaround it using the default provider in request constructor
            }
            $request = new Request($params);
            $failure['url'] = trim($request->getParam('url'));
            $failure['problem'] = '';
            $failure['solution'] = '';
            $failure['solution_url'] = '';

            switch ($failure['idfailure']) {
                case self::FAILURE_ID_INVALID_SITE:
                    $failure['problem'] = Piwik::translate('CoreAdminHome_TrackingFailureInvalidSiteProblem');
                    $failure['solution'] = Piwik::translate('CoreAdminHome_TrackingFailureInvalidSiteSolution');
                    $failure['solution_url'] = 'https://matomo.org/faq/how-to/faq_30838/';
                    break;
                case self::FAILURE_ID_NOT_AUTHENTICATED:
                    $failure['problem'] = Piwik::translate('CoreAdminHome_TrackingFailureAuthenticationProblem');
                    $failure['solution'] = Piwik::translate('CoreAdminHome_TrackingFailureAuthenticationSolution');
                    $failure['solution_url'] = 'https://matomo.org/faq/how-to/faq_30835/';
                    break;
            }
        }

        /**
         * @ignore
         * internal use only
         */
        Piwik::postEvent('Tracking.makeFailuresHumanReadable', array(&$failures));

        return $failures;
    }
}
