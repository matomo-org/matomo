<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitorGenerator
 */
namespace Piwik\Plugins\VisitorGenerator;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Controller\Admin;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Date;
use Piwik\Http;
use Piwik\Nonce;
use Piwik\View;
use Piwik\Url;
use Piwik\Timer;
use Piwik\Site;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

/**
 *
 * @package VisitorGenerator
 */
class Controller extends Admin
{
    public function index()
    {
        Piwik::checkUserIsSuperUser();

        $sitesList = SitesManagerAPI::getInstance()->getSitesWithAdminAccess();

        $view = new View('@VisitorGenerator/index');
        $this->setBasicVariablesView($view);
        $view->assign('sitesList', $sitesList);
        $view->nonce = Nonce::getNonce('VisitorGenerator.generate');
        $view->countActionsPerRun = count($this->getAccessLog());
        $view->accessLogPath = $this->getAccessLogPath();
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
        $nonce = Common::getRequestVar('form_nonce', '', 'string', $_POST);
        if (Common::getRequestVar('choice', 'no') != 'yes' ||
            !Nonce::verifyNonce('VisitorGenerator.generate', $nonce)
        ) {
            Piwik::redirectToModule('VisitorGenerator', 'index');
        }
        Nonce::discardNonce('VisitorGenerator.generate');

        $daysToCompute = Common::getRequestVar('daysToCompute', 1, 'int');

        // get idSite from POST with fallback to GET
        $idSite = Common::getRequestVar('idSite', false, 'int', $_GET);
        $idSite = Common::getRequestVar('idSite', $idSite, 'int', $_POST);

        Piwik::setMaxExecutionTime(0);

        $timer = new Timer;
        $time = time() - ($daysToCompute - 1) * 86400;

        $nbActionsTotal = 0;
        $dates = array();
        while ($time <= time()) {
            $nbActionsTotalThisDay = $this->generateVisits($time, $idSite);
            $dates[] = date("Y-m-d", $time);
            $time += 86400;
            $nbActionsTotal += $nbActionsTotalThisDay;
        }

        $api = CoreAdminHomeAPI::getInstance();
        $api->invalidateArchivedReports($idSite, implode($dates, ","));

        $browserArchiving = Rules::isBrowserTriggerEnabled();

        // Init view
        $view = new View('@VisitorGenerator/generate');
        $this->setBasicVariablesView($view);
        $view->assign('browserArchivingEnabled', $browserArchiving);
        $view->assign('timer', $timer);
        $view->assign('days', $daysToCompute);
        $view->assign('nbActionsTotal', $nbActionsTotal);
        $view->assign('nbRequestsPerSec', round($nbActionsTotal / $timer->getTime(), 0));
        $view->assign('siteName', Site::getNameFor($idSite));
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
        $prefix = Url::getCurrentUrlWithoutFileName() . "piwik.php";
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
            $datetime = $date . " " . Date::factory($time)->toString("H:i:s");
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

            if ($output = Http::sendHttpRequest($url, $timeout = 5, $ua, $path = null, $follow = 0, $acceptLanguage)) {
                $count++;
            }
        }
        return $count;
    }
}
