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
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\ProxyHttp;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{

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

        $view->idSite = $this->idSite;
        $view->date = Common::getRequestVar('date', 'today');
        $view->period = Common::getRequestVar('period', 'day');

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
        $currentUrl = Common::unsanitizeInputValue($currentUrl);

        $normalizedCurrentUrl = PageUrl::excludeQueryParametersFromUrl($currentUrl, $idSite);
        $normalizedCurrentUrl = Common::unsanitizeInputValue($normalizedCurrentUrl);

        // load the appropriate row of the page urls report using the label filter
        ArchivingHelper::reloadConfig();
        $path = ArchivingHelper::getActionExplodedNames($normalizedCurrentUrl, Action::TYPE_PAGE_URL);
        $path = array_map('urlencode', $path);
        $label = implode('>', $path);
        $request = new Request(
            'method=Actions.getPageUrls'
            . '&idSite=' . urlencode($idSite)
            . '&date=' . urlencode($date)
            . '&period=' . urlencode($period)
            . '&label=' . urlencode($label)
            . '&format=original'
        );
        $dataTable = $request->process();

        $data = array();
        if ($dataTable->getRowsCount() > 0) {
            $row = $dataTable->getFirstRow();

            $translations = Metrics::getDefaultMetricTranslations();
            $showMetrics = array('nb_hits', 'nb_visits', 'nb_users', 'nb_uniq_visitors',
                                 'bounce_rate', 'exit_rate', 'avg_time_on_page');

            foreach ($showMetrics as $metric) {
                $value = $row->getColumn($metric);
                if ($value === false) {
                    // skip unique visitors for period != day
                    continue;
                }
                if ($metric == 'avg_time_on_page') {
                    $value = MetricsFormatter::getPrettyTimeFromSeconds($value);
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

        $sitesManager = APISitesManager::getInstance();
        $site = $sitesManager->getSiteFromId($idSite);
        $urls = $sitesManager->getSiteUrlsFromId($idSite);

        $this->outputCORSHeaders();
        Common::sendHeader('Content-Type: text/html; charset=UTF-8');
        return '
			<html><head><title></title></head><body>
			<script type="text/javascript">
				function handleProtocol(url) {
					if (' . (ProxyHttp::isHttps() ? 'true' : 'false') . ') {
						return url.replace(/http:\/\//i, "https://");
					} else {
						return url.replace(/https:\/\//i, "http://");
					}
				}

				function removeUrlPrefix(url) {
					return url.replace(/http(s)?:\/\/(www\.)?/i, "");
				}

				if (window.location.hash) {
					var match = false;

					var urlToRedirect = window.location.hash.substr(1);
					var urlToRedirectWithoutPrefix = removeUrlPrefix(urlToRedirect);

					var knownUrls = ' . Common::json_encode($urls) . ';
					for (var i = 0; i < knownUrls.length; i++) {
						var testUrl = removeUrlPrefix(knownUrls[i]);
						if (urlToRedirectWithoutPrefix.substr(0, testUrl.length) == testUrl) {
							match = true;
							if (navigator.appName == "Microsoft Internet Explorer") {
								// internet explorer loses the referrer if we use window.location.href=X
								var referLink = document.createElement("a");
								referLink.href = handleProtocol(urlToRedirect);
								document.body.appendChild(referLink);
								referLink.click();
							} else {
								window.location.href = handleProtocol(urlToRedirect);
							}
							break;
						}
					}

					if (!match) {
						var idSite = window.location.href.match(/idSite=([0-9]+)/i)[1];
						window.location.href = "index.php?module=Overlay&action=showErrorWrongDomain"
							+ "&idSite=" + idSite
							+ "&url=" + encodeURIComponent(urlToRedirect);
					}
				}
				else {
					window.location.href = handleProtocol("' . $site['main_url'] . '");
				};
			</script>
			</body></html>
		';
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
