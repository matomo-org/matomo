<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Common;
use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

class VisitorDetails extends VisitorDetailsAbstract
{
    const EVENT_VALUE_PRECISION = 3;

    public function extendVisitorDetails(&$visitor)
    {
        $visitor['searches']     = $this->details['visit_total_searches'];
        $visitor['actions']      = $this->details['visit_total_actions'];
        $visitor['interactions'] = $this->details['visit_total_interactions'];
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        $formatter = new Formatter();

        if ($action['type'] == Action::TYPE_EVENT) {
            // Handle Event
            if (strlen($action['pageTitle']) > 0) {
                $action['eventName'] = $action['pageTitle'];
            }

            unset($action['pageTitle']);

        } else if ($action['type'] == Action::TYPE_SITE_SEARCH) {
            // Handle Site Search
            $action['siteSearchKeyword'] = $action['pageTitle'];
            unset($action['pageTitle']);
        }

        // Event value / Generation time
        if ($action['type'] == Action::TYPE_EVENT) {
            if (strlen($action['custom_float']) > 0) {
                $action['eventValue'] = round($action['custom_float'], self::EVENT_VALUE_PRECISION);
            }
        } elseif (isset($action['custom_float']) && $action['custom_float'] > 0) {
            $action['generationTimeMilliseconds'] = $action['custom_float'];
            $action['generationTime'] = $formatter->getPrettyTimeFromSeconds($action['custom_float'] / 1000, true);
        }
        unset($action['custom_float']);

        if ($action['type'] != Action::TYPE_EVENT) {
            unset($action['eventCategory']);
            unset($action['eventAction']);
        }

        if (array_key_exists('interaction_position', $action)) {
            $action['interactionPosition'] = $action['interaction_position'];
            unset($action['interaction_position']);
        }

        // Reconstruct url from prefix
        if (array_key_exists('url', $action) && array_key_exists('url_prefix', $action)) {
            $url = PageUrl::reconstructNormalizedUrl($action['url'], $action['url_prefix']);
            $url = Common::unsanitizeInputValue($url);

            $action['url'] = $url;
            unset($action['url_prefix']);
        }

        switch ($action['type']) {
            case 'goal':
                $action['icon'] = 'plugins/Morpheus/images/goal.png';
                break;
            case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER:
            case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART:
                $action['icon'] = 'plugins/Morpheus/images/' . $action['type'] . '.png';
                break;
            case Action::TYPE_DOWNLOAD:
                $action['type'] = 'download';
                $action['icon'] = 'plugins/Morpheus/images/download.png';
                break;
            case Action::TYPE_OUTLINK:
                $action['type'] = 'outlink';
                $action['icon'] = 'plugins/Morpheus/images/link.png';
                break;
            case Action::TYPE_SITE_SEARCH:
                $action['type'] = 'search';
                $action['icon'] = 'plugins/Morpheus/images/search_ico.png';
                break;
            case Action::TYPE_EVENT:
                $action['type'] = 'event';
                $action['icon'] = 'plugins/Morpheus/images/event.png';
                break;
            default:
                $action['type'] = 'action';
                $action['icon'] = null;
                break;
        }

        // Convert datetimes to the site timezone
        $dateTimeVisit = Date::factory($action['serverTimePretty'], Site::getTimezoneFor($visitorDetails['idSite']));
        $action['serverTimePretty'] = $dateTimeVisit->getLocalized(Date::DATETIME_FORMAT_SHORT);
        $action['timestamp'] = $dateTimeVisit->getTimestamp();

        unset($action['idlink_va']);
    }
}