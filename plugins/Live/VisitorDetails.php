<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\Date;
use Piwik\Network\IPUtils;
use Piwik\Site;
use Piwik\Plugins\SitesManager\API as APISitesManager;
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

        $visitor += array(
            'idSite'              => $idSite,
            'idVisit'             => $this->getIdVisit(),
            'visitIp'             => $this->getIp(),
            'visitorId'           => $this->getVisitorId(),

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
        );

        $visitor['siteCurrency']         = $currency;
        $visitor['siteCurrencySymbol']   = @$currencies[$visitor['siteCurrency']];
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
        switch ($action['type']) {
            case 'ecommerceOrder':
            case 'ecommerceAbandonedCart':
                $template = '@Live/_actionEcommerce.twig';
                break;
            case 'goal':
                $template = '@Live/_actionGoal.twig';
                break;
            case 'action':
            case 'search':
            case 'outlink':
            case 'download':
            case 'event':
                $template = '@Live/_actionCommon.twig';
                break;
        }

        if (empty($template)) {
            return;
        }

        $view                 = new View($template);
        $view->action         = $action;
        $view->previousAction = $previousAction;
        $view->visitInfo      = $visitorDetails;
        return $view->render();
    }

    public function renderActionTooltip($action, $visitInfo)
    {
        $view            = new View('@Live/_actionTooltip');
        $view->action    = $action;
        $view->visitInfo = $visitInfo;
        return $view->render();
    }

    public function renderVisitorDetails($visitorDetails)
    {
        $view            = new View('@Live/_visitorDetails.twig');
        $view->visitInfo = $visitorDetails;
        return $view->render();
    }

    public function renderIcons($visitorDetails)
    {
        $view          = new View('@Live/_visitorLogIcons.twig');
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
        return $this->details['idsite'];
    }

    function getTimestampLastAction()
    {
        return strtotime($this->details['visit_last_action_time']);
    }

    function getDateTimeLastAction()
    {
        return date('Y-m-d H:i:s', strtotime($this->details['visit_last_action_time']));
    }
}