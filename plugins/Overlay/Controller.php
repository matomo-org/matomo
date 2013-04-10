<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Overlay
 */

class Piwik_Overlay_Controller extends Piwik_Controller
{

    /** The index of the plugin */
    public function index()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        $template = 'index';
        if (Piwik_Config::getInstance()->General['overlay_disable_framed_mode']) {
            $template = 'index_noframe';
        }

        $view = Piwik_View::factory($template);

        $this->setGeneralVariablesView($view);
        $view->showTopMenu = false;
        $view->showSitesSelection = false;
        $view->addToHead = '<script type="text/javascript" src="plugins/Overlay/templates/index.js"></script>'
            . '<link rel="stylesheet" type="text/css" href="plugins/Overlay/templates/index.css" />';

        $view->idSite = $this->idSite;
        $view->date = Piwik_Common::getRequestVar('date', 'today');
        $view->period = Piwik_Common::getRequestVar('period', 'day');

        $view->ssl = Piwik::isHttps();

        echo $view->render();
    }

    /** Render the area left of the iframe */
    public function renderSidebar()
    {
        $idSite = Piwik_Common::getRequestVar('idSite');
        $period = Piwik_Common::getRequestVar('period');
        $date = Piwik_Common::getRequestVar('date');
        $currentUrl = Piwik_Common::getRequestVar('currentUrl');
        $currentUrl = Piwik_Common::unsanitizeInputValue($currentUrl);

        $normalizedCurrentUrl = Piwik_Tracker_Action::excludeQueryParametersFromUrl($currentUrl, $idSite);
        $normalizedCurrentUrl = Piwik_Common::unsanitizeInputValue($normalizedCurrentUrl);

        // load the appropriate row of the page urls report using the label filter
        Piwik_Actions_ArchivingHelper::reloadConfig();
        $path = Piwik_Actions_ArchivingHelper::getActionExplodedNames($normalizedCurrentUrl, Piwik_Tracker_Action::TYPE_ACTION_URL);
        $path = array_map('urlencode', $path);
        $label = implode('>', $path);
        $request = new Piwik_API_Request(
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

            $translations = Piwik_API_API::getDefaultMetricTranslations();
            $showMetrics = array('nb_hits', 'nb_visits', 'nb_uniq_visitors',
                                 'bounce_rate', 'exit_rate', 'avg_time_on_page');


            foreach ($showMetrics as $metric) {
                $value = $row->getColumn($metric);
                if ($value === false) {
                    // skip unique visitors for period != day
                    continue;
                }
                if ($metric == 'avg_time_on_page') {
                    $value = Piwik::getPrettyTimeFromSeconds($value);
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
        $view = Piwik_View::factory('sidebar');
        $view->data = $data;
        $view->location = $page;
        $view->normalizedUrl = $normalizedCurrentUrl;
        $view->label = $label;
        $view->idSite = $idSite;
        $view->period = $period;
        $view->date = $date;
        echo $view->render();
    }

    /**
     * Start an Overlay session: Redirect to the tracked website. The Piwik
     * tracker will recognize this referrer and start the session.
     */
    public function startOverlaySession()
    {
        $idSite = Piwik_Common::getRequestVar('idsite', 0, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $sitesManager = Piwik_SitesManager_API::getInstance();
        $site = $sitesManager->getSiteFromId($idSite);
        $urls = $sitesManager->getSiteUrlsFromId($idSite);

        @header('Content-Type: text/html; charset=UTF-8');
        echo '
			<html><head><title></title></head><body>
			<script type="text/javascript">
				function handleProtocol(url) {
					if (' . (Piwik::isHttps() ? 'true' : 'false') . ') {
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
					
					var knownUrls = ' . Piwik_Common::json_encode($urls) . ';
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
        $idSite = Piwik_Common::getRequestVar('idSite', 0, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $url = Piwik_Common::getRequestVar('url', '');
        $url = Piwik_Common::unsanitizeInputValue($url);

        $message = Piwik_Translate('Overlay_RedirectUrlError', array($url, "\n"));
        $message = nl2br(htmlentities($message));

        $view = Piwik_View::factory('error_wrong_domain');
        $view->message = $message;

        if (Piwik::isUserHasAdminAccess($idSite)) {
            // TODO use $idSite to link to the correct row. This is tricky because the #rowX ids don't match
            // the site ids when sites have been deleted.
            $url = 'index.php?module=SitesManager&action=index';
            $troubleshoot = htmlentities(Piwik_Translate('Overlay_RedirectUrlErrorAdmin'));
            $troubleshoot = sprintf($troubleshoot, '<a href="' . $url . '" target="_top">', '</a>');
            $view->troubleshoot = $troubleshoot;
        } else {
            $view->troubleshoot = htmlentities(Piwik_Translate('Overlay_RedirectUrlErrorUser'));
        }

        echo $view->render();
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
        $view = Piwik_View::factory('notify_parent_iframe');
        echo $view->render();
    }

}
