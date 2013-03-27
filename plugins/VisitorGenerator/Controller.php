<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitorGenerator
 */

/**
 *
 * @package Piwik_VisitorGenerator
 */
class Piwik_VisitorGenerator_Controller extends Piwik_Controller_Admin
{
    public function index()
    {
        Piwik::checkUserIsSuperUser();

        $sitesList = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();

        $view = Piwik_View::factory('index');
        $this->setBasicVariablesView($view);
        $view->assign('sitesList', $sitesList);
        $view->nonce = Piwik_Nonce::getNonce('Piwik_VisitorGenerator.generate');
        $view->countActionsPerRun = count($this->getAccessLog());
        $view->accessLogPath = $this->getAccessLogPath();
        $view->menu = Piwik_GetAdminMenu();
        echo $view->render();
    }

    private function getAccessLogPath()
    {
        return PIWIK_INCLUDE_PATH . "/plugins/VisitorGenerator/data/access.log";
    }

    private function getAccessLog()
    {
        $log = file($this->getAccessLogPath());
        return $log;
    }

    public function generate()
    {
        Piwik::checkUserIsSuperUser();
        $nonce = Piwik_Common::getRequestVar('form_nonce', '', 'string', $_POST);
        if (Piwik_Common::getRequestVar('choice', 'no') != 'yes' ||
            !Piwik_Nonce::verifyNonce('Piwik_VisitorGenerator.generate', $nonce)
        ) {
            Piwik::redirectToModule('VisitorGenerator', 'index');
        }
        Piwik_Nonce::discardNonce('Piwik_VisitorGenerator.generate');

        $daysToCompute = Piwik_Common::getRequestVar('daysToCompute', 1, 'int');

        // get idSite from POST with fallback to GET
        $idSite = Piwik_Common::getRequestVar('idSite', false, 'int', $_GET);
        $idSite = Piwik_Common::getRequestVar('idSite', $idSite, 'int', $_POST);

        Piwik::setMaxExecutionTime(0);

        $timer = new Piwik_Timer;
        $time = time() - ($daysToCompute - 1) * 86400;

        $nbActionsTotal = 0;
        $dates = array();
        while ($time <= time()) {
            $nbActionsTotalThisDay = $this->generateVisits($time, $idSite);
            $dates[] = date("Y-m-d", $time);
            $time += 86400;
            $nbActionsTotal += $nbActionsTotalThisDay;
        }

        $api = Piwik_CoreAdminHome_API::getInstance();
        $api->invalidateArchivedReports($idSite, implode($dates, ","));

        $browserArchiving = Piwik_ArchiveProcessing::isBrowserTriggerArchivingEnabled();

        // Init view
        $view = Piwik_View::factory('generate');
        $this->setBasicVariablesView($view);
        $view->menu = Piwik_GetAdminMenu();
        $view->assign('browserArchivingEnabled', $browserArchiving);
        $view->assign('timer', $timer);
        $view->assign('days', $daysToCompute);
        $view->assign('nbActionsTotal', $nbActionsTotal);
        $view->assign('nbRequestsPerSec', round($nbActionsTotal / $timer->getTime(), 0));
        $view->assign('siteName', Piwik_Site::getNameFor($idSite));
        echo $view->render();
    }

    private function generateVisits($time = false, $idSite = 1)
    {
        $logs = $this->getAccessLog();
        if (empty($time)) $time = time();
        $date = date("Y-m-d", $time);

        $acceptLanguages = array(
            "el,fi;q=0.5",
            "de-de,de;q=0.8,en-us",
            "pl,en-us;q=0.7,en;q=",
            "zh-cn",
            "fr-ca",
            "en-us",
            "en-gb",
            "fr-be",
            "fr,de-ch;q=0.5",
            "fr",
            "fr-ch",
            "fr",
        );
        $prefix = Piwik_Url::getCurrentUrlWithoutFileName() . "piwik.php";
        $count = 0;
        foreach ($logs as $log) {
            if (!preg_match('/^(\S+) \S+ \S+ \[(.*?)\] "GET (\S+.*?)" \d+ \d+ "(.*?)" "(.*?)"/', $log, $m)) {
                continue;
            }
            $ip = $m[1];
            $time = $m[2];
            $url = $m[3];
            $referrer = $m[4];
            $ua = $m[5];

            $start = strpos($url, 'piwik.php?') + strlen('piwik.php?');
            $url = substr($url, $start, strrpos($url, " ") - $start);
            $datetime = $date . " " . Piwik_Date::factory($time)->toString("H:i:s");
            $ip = strlen($ip) < 10 ? "13.5.111.3" : $ip;

            // Force date/ip & authenticate
            $url .= "&cdt=" . urlencode($datetime);
            if (strpos($url, 'cip') === false) {
                $url .= "&cip=" . $ip;
            }
            $url .= "&token_auth=" . Piwik::getCurrentUserTokenAuth();
            $url = $prefix . "?" . $url;

            // Make order IDs unique per day
            $url = str_replace("ec_id=", "ec_id=$date-", $url);

            // Disable provider plugin
            $url .= "&dp=1";

            // Replace idsite
            $url = preg_replace("/idsite=[0-9]+/", "idsite=$idSite", $url);

            $acceptLanguage = $acceptLanguages[$count % count($acceptLanguages)];

            if ($output = Piwik_Http::sendHttpRequest($url, $timeout = 5, $ua, $path = null, $follow = 0, $acceptLanguage)) {
                $count++;
            }
        }
        return $count;
    }
}
