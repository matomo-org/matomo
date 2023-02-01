<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Live;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Matomo\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\Referrers\API as APIReferrers;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $idSite     = $this->getIdSite();
        $website    = new Site($idSite);
        $timezone   = $website->getTimezone();
        $currency   = $website->getCurrency();
        $currencies = APISitesManager::getInstance()->getCurrencySymbols();

        $visitor += [
            'idSite'              => $idSite,
            'idVisit'             => $this->getIdVisit(),
            'visitIp'             => $this->getIp(),
            'visitorId'           => $this->getVisitorId(),
            'fingerprint'         => $this->getFingerprint(),

            // => false are placeholders to be filled in API later
            'actionDetails'       => false,
            'goalConversions'     => false,
            'siteCurrency'        => false,
            'siteCurrencySymbol'  => false,

            // all time entries
            'serverDate'          => $this->getServerDate(),
            'visitServerHour'     => $this->getVisitServerHour(),
            'lastActionTimestamp' => $this->getTimestampLastAction(),
            'lastActionDateTime'  => $this->getDateTimeLastAction(),
        ];

        $visitor['siteCurrency']         = $currency;
        $visitor['siteCurrencySymbol']   = @$currencies[$visitor['siteCurrency']];
        $visitor['siteName']             = $website->getName();
        $visitor['serverTimestamp']      = $visitor['lastActionTimestamp'];
        $visitor['firstActionTimestamp'] = strtotime($this->details['visit_first_action_time']);

        $dateTimeVisit = Date::factory($visitor['lastActionTimestamp'], $timezone);
        if ($dateTimeVisit) {
            $visitor['serverTimePretty'] = $dateTimeVisit->getLocalized(Date::TIME_FORMAT);
            $visitor['serverDatePretty'] = $dateTimeVisit->getLocalized(Date::DATE_FORMAT_LONG);
        }

        $dateTimeVisitFirstAction               = Date::factory($visitor['firstActionTimestamp'], $timezone);
        $visitor['serverDatePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized(Date::DATE_FORMAT_LONG);
        $visitor['serverTimePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized(Date::TIME_FORMAT);
    }

    public function renderAction($action, $previousAction, $visitorDetails)
    {
        if (empty($action['type'])) {
            return;
        }

        switch ($action['type']) {
            case 'ecommerceOrder':
            case 'ecommerceAbandonedCart':
                $template = '@Live/_actionEcommerce.twig';
                break;
            case 'goal':
                if (empty($action['goalName'])) {
                    return; // goal deleted
                }
                $template = '@Live/_actionGoal.twig';
                break;
            case 'action':
            case 'search':
            case 'outlink':
            case 'download':
                $template = '@Live/_actionCommon.twig';
                break;
        }

        if (empty($template)) {
            return;
        }

        $sitesModel = new \Piwik\Plugins\SitesManager\Model();

        if (isset($action['type']) && in_array($action['type'] ,['outlink', 'download']) && isset($action['url'])) {
            $action['url'] = html_entity_decode($action['url'], ENT_QUOTES, "UTF-8");
        }

        $view                 = new View($template);
        $view->sendHeadersWhenRendering = false;
        $view->mainUrl        = trim(Site::getMainUrlFor($this->getIdSite()));
        $view->additionalUrls = $sitesModel->getAliasSiteUrlsFromId($this->getIdSite());
        $view->action         = $action;
        $view->previousAction = $previousAction;
        $view->visitInfo      = $visitorDetails;
        return $view->render();
    }

    public function renderActionTooltip($action, $visitInfo)
    {
        $view            = new View('@Live/_actionTooltip');
        $view->sendHeadersWhenRendering = false;
        $view->action    = $action;
        $view->visitInfo = $visitInfo;
        return [[ 0, $view->render() ]];
    }

    public function renderVisitorDetails($visitorDetails)
    {
        $view            = new View('@Live/_visitorDetails.twig');
        $view->isProfileEnabled = Live::isVisitorProfileEnabled();
        $view->sendHeadersWhenRendering = false;
        $view->visitInfo = $visitorDetails;
        return [[ 0, $view->render() ]];
    }

    public function renderIcons($visitorDetails)
    {
        $view          = new View('@Live/_visitorLogIcons.twig');
        $view->isProfileEnabled = Live::isVisitorProfileEnabled();
        $view->sendHeadersWhenRendering = false;
        $view->visitor = $visitorDetails;
        return $view->render();
    }

    function getVisitorId()
    {
        if (isset($this->details['idvisitor'])) {
            return bin2hex($this->details['idvisitor']);
        }
        return false;
    }

    function getVisitServerHour()
    {
        return date('G', strtotime($this->details['visit_last_action_time']));
    }

    function getServerDate()
    {
        return date('Y-m-d', strtotime($this->details['visit_last_action_time']));
    }

    function getIp()
    {
        if (isset($this->details['location_ip'])) {
            return IPUtils::binaryToStringIP($this->details['location_ip']);
        }
        return null;
    }

    function getIdVisit()
    {
        return $this->details['idvisit'];
    }

    function getIdSite()
    {
        return isset($this->details['idsite']) ? $this->details['idsite'] : Common::getRequestVar('idSite');
    }

    function getFingerprint()
    {
        if (isset($this->details['config_id'])) {
            return bin2hex($this->details['config_id']);
        }
        return false;
    }

    function getTimestampLastAction()
    {
        return strtotime($this->details['visit_last_action_time']);
    }

    function getDateTimeLastAction()
    {
        return date('Y-m-d H:i:s', strtotime($this->details['visit_last_action_time']));
    }


    public function initProfile($visits, &$profile)
    {
        $profile['totalVisits']        = 0;
        $profile['totalVisitDuration'] = 0;
    }

    public function handleProfileVisit($visit, &$profile)
    {
        ++$profile['totalVisits'];

        $profile['totalVisitDuration'] += $visit->getColumn('visitDuration');
    }


    public function finalizeProfile($visits, &$profile)
    {
        $formatter                           = new Formatter();
        $profile['totalVisitDurationPretty'] = $formatter->getPrettyTimeFromSeconds($profile['totalVisitDuration'], true);

        $rows                        = $visits->getRows();

        $firstVisit = $profile['visit_first'];
        if (count($rows) >= Config::getInstance()->General['live_visitor_profile_max_visits_to_aggregate']) {
            $firstVisit = $this->fetchFirstVisit();
        }

        $profile['userId']           = $visits->getLastRow()->getColumn('userId');
        $profile['firstVisit']       = $this->getVisitorProfileVisitSummary($firstVisit);
        $profile['lastVisit']        = $this->getVisitorProfileVisitSummary($profile['visit_last']);
        $profile['visitsAggregated'] = count($rows);
    }

    /**
     * Fetch first visit from Live API
     *
     * @return DataTable\Row
     */
    private function fetchFirstVisit()
    {
        $response = Request::processRequest('Live.getFirstVisitForVisitorId', [
            'idSite' => $this->getIdSite(),
            'visitorId' => $this->getVisitorId(),
        ]);

        return $response->getFirstRow();
    }

    /**
     * Returns a summary for an important visit. Used to describe the first & last visits of a visitor.
     *
     * @param DataTable\Row $visit
     * @return array
     */
    private function getVisitorProfileVisitSummary($visit)
    {
        $today = Date::today();

        $serverDate = $visit->getColumn('firstActionTimestamp');
        return [
            'date'            => $serverDate,
            'prettyDate'      => Date::factory($serverDate)->getLocalized(Date::DATE_FORMAT_LONG),
            'daysAgo'         => (int)Date::secondsToDays($today->getTimestamp() - Date::factory($serverDate)->getTimestamp()),
            'referrerType'    => $visit->getColumn('referrerType'),
            'referrerUrl'     => $visit->getColumn('referrerUrl') ?: '',
            'referralSummary' => self::getReferrerSummaryForVisit($visit),
        ];
    }

    /**
     * Returns a summary for a visit's referral.
     *
     * @param DataTable\Row $visit
     * @return bool|mixed|string
     */
    public static function getReferrerSummaryForVisit($visit)
    {
        $referrerType = $visit->getColumn('referrerType');
        if (
            $referrerType === false
            || $referrerType == 'direct'
        ) {
            return Piwik::translate('Referrers_DirectEntry');
        }

        if ($referrerType == 'search') {
            $referrerName = $visit->getColumn('referrerName');

            $keyword = $visit->getColumn('referrerKeyword');
            if (
                $keyword !== false
                && $keyword != APIReferrers::getKeywordNotDefinedString()
            ) {
                $referrerName .= ' (' . $keyword . ')';
            }
            return $referrerName;
        }

        if ($referrerType == 'campaign') {
            $summary = Piwik::translate('Referrers_ColumnCampaign') . ': ' . $visit->getColumn('referrerName');
            $keyword = $visit->getColumn('referrerKeyword');
            if (!empty($keyword)) {
                $summary .= ' - ' . $keyword;
            }

            return $summary;
        }

        return $visit->getColumn('referrerName');
    }
}
