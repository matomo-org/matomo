<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Overlay;

use Piwik\API\CORSHandler;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\SegmentEditor\SegmentFormatter;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\ProxyHttp;
use Piwik\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var SegmentFormatter
     */
    private $segmentFormatter;

    public function __construct(SegmentFormatter $segmentFormatter)
    {
        $this->segmentFormatter = $segmentFormatter;
        parent::__construct();
    }

    /** The index of the plugin */
    public function index()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        $template = '@Overlay/index';
        if (Config::getInstance()->General['overlay_disable_framed_mode']) {
            $template = '@Overlay/index_noframe';
        }

        $view = new View($template);

        $this->setGeneralVariablesView($view);
        $view->segment = Request::getRawSegmentFromRequest();

        $view->ssl = ProxyHttp::isHttps();

        $this->outputCORSHeaders();
        return $view->render();
    }

    /** Render the area left of the iframe */
    public function renderSidebar()
    {
        $idSite = Common::getRequestVar('idSite');
        $period = Common::getRequestVar('period');
        $date = Common::getRequestVar('date');
        $currentUrl = Common::getRequestVar('currentUrl');
        $segment = Request::getRawSegmentFromRequest();
        $currentUrl = Common::unsanitizeInputValue($currentUrl);
        $segmentSidebar = '';

        $normalizedCurrentUrl = PageUrl::excludeQueryParametersFromUrl($currentUrl, $idSite);
        $normalizedCurrentUrl = Common::unsanitizeInputValue($normalizedCurrentUrl);

        // load the appropriate row of the page urls report using the label filter
        ArchivingHelper::reloadConfig();
        $path = ArchivingHelper::getActionExplodedNames($normalizedCurrentUrl, Action::TYPE_PAGE_URL);
        $path = array_map('urlencode', $path);
        $label = implode('>', $path);

        $params = array(
            'idSite' => $idSite,
            'date' => $date,
            'period' => $period,
            'label' => $label,
            'format' => 'original',
            'format_metrics' => 0,
        );

        if (!empty($segment)) {
            $params['segment'] = $segment;
        }

        $dataTable = Request::processRequest('Actions.getPageUrls', $params);

        $formatter = new Metrics\Formatter\Html();

        $data = array();
        if ($dataTable->getRowsCount() > 0) {
            $row = $dataTable->getFirstRow();

            $translations = Metrics::getDefaultMetricTranslations();
            $showMetrics = array('nb_hits', 'nb_visits', 'nb_users', 'nb_uniq_visitors',
                                 'bounce_rate', 'exit_rate', 'avg_time_on_page');

            $segmentSidebar = $row->getMetadata('segment');
            if (!empty($segmentSidebar) && !empty($segment)) {
                $segmentSidebar = $segment . ';' . $segmentSidebar;
            }

            foreach ($showMetrics as $metric) {
                $value = $row->getColumn($metric);
                if ($value === false) {
                    // skip unique visitors for period != day
                    continue;
                }

                if ($metric == 'bounce_rate'
                    || $metric == 'exit_rate'
                ) {
                    $value = $formatter->getPrettyPercentFromQuotient($value);
                } else if ($metric == 'avg_time_on_page') {
                    $value = $formatter->getPrettyTimeFromSeconds($value, $displayAsSentence = true);
                }

                $data[] = array(
                    'name'  => $translations[$metric],
                    'value' => $value
                );
            }
        }

        // generate page url string
        foreach ($path as &$part) {
            $part = preg_replace(';^/;', '', urldecode($part));
        }
        $page = '/' . implode('/', $path);
        $page = preg_replace(';/index$;', '/', $page);
        if ($page == '/') {
            $page = '/index';
        }

        // render template
        $view = new View('@Overlay/renderSidebar');
        $view->data = $data;
        $view->location = $page;
        $view->normalizedUrl = $normalizedCurrentUrl;
        $view->label = $label;
        $view->idSite = $idSite;
        $view->period = $period;
        $view->date = $date;
        $view->segment = $segmentSidebar;
        $view->segmentDescription = $this->segmentFormatter->getHumanReadable($segment, $idSite);

        $this->outputCORSHeaders();
        return $view->render();
    }

    /**
     * Start an Overlay session: Redirect to the tracked website. The Piwik
     * tracker will recognize this referrer and start the session.
     */
    public function startOverlaySession()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $view = new View('@Overlay/startOverlaySession');

        $sitesManager = APISitesManager::getInstance();
        $site = $sitesManager->getSiteFromId($idSite);
        $urls = $sitesManager->getSiteUrlsFromId($idSite);

        $view->isHttps   = ProxyHttp::isHttps();
        $view->knownUrls = json_encode($urls);
        $view->mainUrl   = $site['main_url'];

        $this->outputCORSHeaders();
        Common::sendHeader('Content-Type: text/html; charset=UTF-8');

        return $view->render();
    }

    /**
     * This method is called when the JS from startOverlaySession() detects that the target domain
     * is not configured for the current site.
     */
    public function showErrorWrongDomain()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $url = Common::getRequestVar('url', '');
        $url = Common::unsanitizeInputValue($url);

        $message = Piwik::translate('Overlay_RedirectUrlError', array($url, "\n"));
        $message = nl2br(htmlentities($message));

        $view = new View('@Overlay/showErrorWrongDomain');
        $this->addCustomLogoInfo($view);
        $view->message = $message;

        if (Piwik::isUserHasAdminAccess($idSite)) {
            // TODO use $idSite to link to the correct row. This is tricky because the #rowX ids don't match
            // the site ids when sites have been deleted.
            $url = 'index.php?module=SitesManager&action=index';
            $troubleshoot = htmlentities(Piwik::translate('Overlay_RedirectUrlErrorAdmin'));
            $troubleshoot = sprintf($troubleshoot, '<a href="' . $url . '" target="_top">', '</a>');
            $view->troubleshoot = $troubleshoot;
        } else {
            $view->troubleshoot = htmlentities(Piwik::translate('Overlay_RedirectUrlErrorUser'));
        }

        $this->outputCORSHeaders();
        return $view->render();
    }

    /**
     * This method is used to pass information from the iframe back to Piwik.
     * Due to the same origin policy, we can't do that directly, so we inject
     * an additional iframe in the Overlay session that calls this controller
     * method.
     * The rendered iframe is from the same origin as the Piwik window so we
     * can bypass the same origin policy and call the parent.
     */
    public function notifyParentIframe()
    {
        $view = new View('@Overlay/notifyParentIframe');
        $this->outputCORSHeaders();
        return $view->render();
    }

    protected function outputCORSHeaders()
    {
        $corsHandler = new CORSHandler();
        $corsHandler->handle();
    }
}
