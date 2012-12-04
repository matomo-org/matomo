<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountryMap
 */

/**
 *
 * @package Piwik_UserCountryMap
 */
class Piwik_UserCountryMap_Controller extends Piwik_Controller
{

    function _reqUrl($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false) {
        // use processed reports
        $url = "?module=" . $module
        . "&method=".$module.".".$action."&format=JSON"
        . "&idSite=" . $idSite
        . "&period=" . $period
        . "&date=" . $date
        . "&token_auth=" . $token_auth
        . "&segment=" . Piwik_Common::unsanitizeInputValue(Piwik_Common::getRequestVar('segment', ''))
        . "&enable_filter_excludelowpop=1"
        . "&showRawMetrics=1";

        if ($filter_by_country) {
            $url .= "&filter_column=country"
            . "&filter_sort_column=nb_visits"
            . "&filter_limit=-1"
            . "&filter_pattern=";
        } else {
            $url .= "&filter_limit=-1";
        }
        return $url;
    }

    function _report($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false) {
        return $this->_reqUrl('API', 'getProcessedReport&apiModule='.$module.'&apiAction='.$action, $idSite, $period, $date, $token_auth, $filter_by_country);
    }

    function worldMap()
    {
        if(!Piwik_PluginsManager::getInstance()->isPluginActivated('UserCountry'))
        {
            return '';
        }
        $idSite = Piwik_Common::getRequestVar('idSite', 1, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $period = Piwik_Common::getRequestVar('period');
        $date = Piwik_Common::getRequestVar('date');
        $token_auth = Piwik::getCurrentUserTokenAuth();

        $view = Piwik_View::factory('worldmap');

        // request visits summary
        $request = new Piwik_API_Request(
            'method=VisitsSummary.get&format=JSON'
            . '&idSite=' . $idSite
            . '&period=' . $period
            . '&date=' . $date
            . '&token_auth=' . $token_auth
            . '&filter_limit=-1'
        );
        $view->visitsSummary = $request->process();

        $view->countryDataUrl = $this->_report('UserCountry', 'getCountry', $idSite, $period, $date, $token_auth);
        $view->regionDataUrl = $this->_report('UserCountry', 'getRegion', $idSite, $period, $date, $token_auth, true);
        $view->cityDataUrl = $this->_report('UserCountry', 'getCity', $idSite, $period, $date, $token_auth, true);
        $view->countrySummaryUrl = $this->_reqUrl('VisitsSummary', 'get', $idSite, $period, $date, $token_auth, true);

        $view->metrics = $this->getMetrics($idSite, $period, $date, $token_auth);
        $view->defaultMetric = 'nb_visits';

        // some translations
        $view->localeJSON = json_encode(array(
            'nb_visits' => Piwik_Translate('VisitsSummary_NbVisits'),
            'nb_actions' => Piwik_Translate('VisitsSummary_NbActionsDescription'),
            'nb_actions_per_visit' => Piwik_Translate('VisitsSummary_NbActionsPerVisit'),
            'bounce_rate' => Piwik_Translate('VisitsSummary_NbVisitsBounced'),
            'avg_time_on_site' => Piwik_Translate('VisitsSummary_AverageVisitDuration'),
            'and_n_others' => Piwik_Translate('UserCountryMap_AndNOthers')
        ));

        echo $view->render();
    }

    private function getMetrics($idSite, $period, $date, $token_auth) {
        $request = new Piwik_API_Request(
            'method=API.getMetadata&format=PHP'
            . '&apiModule=UserCountry&apiAction=getCountry'
            . '&idSite=' . $idSite
            . '&period=' . $period
            . '&date=' . $date
            . '&token_auth=' . $token_auth
            . '&filter_limit=-1'
        );
        $metaData = $request->process();

        $metrics = array();
        foreach ($metaData[0]['metrics'] as $id => $val)
        {
            if (Piwik_Common::getRequestVar('period') == 'day' || $id != 'nb_uniq_visitors') {
                $metrics[] = array($id, $val);
            }
        }
        foreach ($metaData[0]['processedMetrics'] as $id => $val) 
        {
            $metrics[] = array($id, $val);
        }
        return $metrics;
    }

    /*
     * shows the traditional extra page where the user
     * is able to download the exported image via right - click
     *
     * note: this is a fallback for older flashplayer versions
     */
    function exportImage()
    {
        Piwik_Proxy_Controller::exportImageWindow();
    }

    /*
     * this outputs the image straight forward and is directly called
     * via flash download process
     */
    function outputImage()
    {
        Piwik_Proxy_Controller::outputBinaryImage();
    }

    /*
     * debug mode for worldmap
     * helps to find JS bugs in IE8
     */
    /*
    function debug()
    {
        echo '<html><head><title>DEBUG: world map</title>';
        echo '<script type="text/javascript" src="libs/jquery/jquery.js"></script>';
        echo '<script type="text/javascript" src="libs/swfobject/swfobject.js"></script>';
        echo '</head><body><div id="widgetUserCountryMapworldMap" style="width:600px;">';
        echo $this->worldMap();
        echo '</div></body></html>';
    }
    // */
}
