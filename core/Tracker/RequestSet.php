<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SiteUrls;
use Piwik\Url;

class RequestSet
{
    /**
     * The set of visits to track.
     *
     * @var Request[]
     */
    private $requests = null;

    /**
     * The token auth supplied with a bulk visits POST.
     *
     * @var string
     */
    private $tokenAuth = null;

    private $env = array();

    public function setRequests($requests)
    {
        $this->requests = array();

        foreach ($requests as $request) {
            if (empty($request) && !is_array($request)) {
                continue;
            }

            if (!$request instanceof Request) {
                $request = new Request($request, $this->getTokenAuth());
            }

            $this->requests[] = $request;
        }
    }

    public function setTokenAuth($tokenAuth)
    {
        $this->tokenAuth = $tokenAuth;
    }

    public function getNumberOfRequests()
    {
        if (is_array($this->requests)) {
            return count($this->requests);
        }

        return 0;
    }

    public function getRequests()
    {
        if (!$this->areRequestsInitialized()) {
            return array();
        }

        return $this->requests;
    }

    public function getTokenAuth()
    {
        if (!is_null($this->tokenAuth)) {
            return $this->tokenAuth;
        }

        return Common::getRequestVar('token_auth', false);
    }

    private function areRequestsInitialized()
    {
        return !is_null($this->requests);
    }

    public function initRequestsAndTokenAuth()
    {
        if ($this->areRequestsInitialized()) {
            return;
        }

        /**
         * Triggered when detecting tracking requests. A plugin can use this event to set
         * requests that should be tracked by calling the {@link RequestSet::setRequests()} method.
         * For example the BulkTracking plugin uses this event to detect tracking requests and auth token based on
         * a sent JSON instead of default $_GET+$_POST. It would allow you for example to track requests based on
         * XML or you could import tracking requests stored in a file.
         *
         * @param \Piwik\Tracker\RequestSet &$requestSet  Call {@link setRequests()} to initialize requests and
         *                                                {@link setTokenAuth()} to set a detected auth token.
         *
         * @ignore This event is not public yet as the RequestSet API is not really stable yet
         */
        Piwik::postEvent('Tracker.initRequestSet', array($this));

        if (!$this->areRequestsInitialized()) {
            $this->requests = array();

            if (!empty($_GET) || !empty($_POST)) {
                $this->setRequests(array($_GET + $_POST));
            }
        }
    }

    public function hasRequests()
    {
        return !empty($this->requests);
    }

    protected function getRedirectUrl()
    {
        return Common::getRequestVar('redirecturl', false, 'string');
    }

    protected function hasRedirectUrl()
    {
        $redirectUrl = $this->getRedirectUrl();

        return !empty($redirectUrl);
    }

    protected function getAllSiteIdsWithinRequest()
    {
        if (empty($this->requests)) {
            return array();
        }

        $siteIds = array();
        foreach ($this->requests as $request) {
            $siteIds[] = (int) $request->getIdSite();
        }

        return array_values(array_unique($siteIds));
    }

    // TODO maybe move to reponse? or somewhere else? not sure where!
    public function shouldPerformRedirectToUrl()
    {
        if (!$this->hasRedirectUrl()) {
            return false;
        }

        if (!$this->hasRequests()) {
            return false;
        }

        $redirectUrl = $this->getRedirectUrl();
        $host        = Url::getHostFromUrl($redirectUrl);

        if (empty($host)) {
            return false;
        }

        $urls     = new SiteUrls();
        $siteUrls = $urls->getAllCachedSiteUrls();
        $siteIds  = $this->getAllSiteIdsWithinRequest();

        foreach ($siteIds as $siteId) {
            if (empty($siteUrls[$siteId])) {
                $siteUrls[$siteId] = array();
            }

            if (Url::isHostInUrls($host, $siteUrls[$siteId])) {
                return $redirectUrl;
            }
        }

        return false;
    }

    public function getState()
    {
        $requests = array(
            'requests'  => array(),
            'env'       => $this->getEnvironment(),
            'tokenAuth' => $this->getTokenAuth(),
            'time'      => time()
        );

        foreach ($this->getRequests() as $request) {
            $requests['requests'][] = $request->getRawParams();
        }

        return $requests;
    }

    public function restoreState($state)
    {
        $backupEnv = $this->getCurrentEnvironment();

        $this->setEnvironment($state['env']);
        $this->setTokenAuth($state['tokenAuth']);

        $this->restoreEnvironment();
        $this->setRequests($state['requests']);

        foreach ($this->getRequests() as $request) {
            $request->setCurrentTimestamp($state['time']);
        }

        $this->resetEnvironment($backupEnv);
    }

    public function rememberEnvironment()
    {
        $this->setEnvironment($this->getEnvironment());
    }

    public function setEnvironment($env)
    {
        $this->env = $env;
    }

    protected function getEnvironment()
    {
        if (!empty($this->env)) {
            return $this->env;
        }

        return $this->getCurrentEnvironment();
    }

    public function restoreEnvironment()
    {
        if (empty($this->env)) {
            return;
        }

        $this->resetEnvironment($this->env);
    }

    private function resetEnvironment($env)
    {
        $_SERVER = $env['server'];
    }

    private function getCurrentEnvironment()
    {
        return array(
            'server' => $_SERVER
        );
    }
}
