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
use Piwik\Config;
use Piwik\DeviceDetectorFactory;
use Piwik\IP;
use Piwik\Piwik;

/**
 * This class contains the logic to exclude some visitors from being tracked as per user settings
 */
class VisitExcluded
{
    /**
     * @param Request $request
     * @param bool|string $ip
     * @param bool|string $userAgent
     */
    public function __construct(Request $request, $ip = false, $userAgent = false)
    {
        if (false === $ip) {
            $ip = $request->getIp();
        }

        if (false === $userAgent) {
            $userAgent = $request->getUserAgent();
        }

        $this->request   = $request;
        $this->idSite    = $request->getIdSite();
        $this->userAgent = $userAgent;
        $this->ip = $ip;
    }

    /**
     * Test if the current visitor is excluded from the statistics.
     *
     * Plugins can for example exclude visitors based on the
     * - IP
     * - If a given cookie is found
     *
     * @return bool True if the visit must not be saved, false otherwise
     */
    public function isExcluded()
    {
        $excluded = false;

        if ($this->isNonHumanBot()) {
            Common::printDebug('Search bot detected, visit excluded');
            $excluded = true;
        }

        /*
         * Requests built with piwik.js will contain a rec=1 parameter. This is used as
         * an indication that the request is made by a JS enabled device. By default, Piwik
         * doesn't track non-JS visitors.
         */
        if (!$excluded) {
            $toRecord = $this->request->getParam($parameterForceRecord = 'rec');
            if (!$toRecord) {
                Common::printDebug(@$_SERVER['REQUEST_METHOD'] . ' parameter ' . $parameterForceRecord . ' not found in URL, request excluded');
                $excluded = true;
                Common::printDebug("'$parameterForceRecord' parameter not found.");
            }
        }

        /**
         * Triggered on every tracking request.
         *
         * This event can be used to tell the Tracker not to record this particular action or visit.
         *
         * @param bool &$excluded Whether the request should be excluded or not. Initialized
         *                        to `false`. Event subscribers should set it to `true` in
         *                        order to exclude the request.
         */
        Piwik::postEvent('Tracker.isExcludedVisit', array(&$excluded));

        /*
         * Following exclude operations happen after the hook.
         * These are of higher priority and should not be overwritten by plugins.
         */

        // Checking if the Piwik ignore cookie is set
        if (!$excluded) {
            $excluded = $this->isIgnoreCookieFound();
            if ($excluded) {
                Common::printDebug("Ignore cookie found.");
            }
        }

        // Checking for excluded IPs
        if (!$excluded) {
            $excluded = $this->isVisitorIpExcluded();
            if ($excluded) {
                Common::printDebug("IP excluded.");
            }
        }

        // Check if user agent should be excluded
        if (!$excluded) {
            $excluded = $this->isUserAgentExcluded();
            if ($excluded) {
                Common::printDebug("User agent excluded.");
            }
        }

        // Check if Referrer URL is a known spam
        if (!$excluded) {
            $excluded = $this->isReferrerSpamExcluded();
            if ($excluded) {
                Common::printDebug("Referrer URL is blacklisted as spam.");
            }
        }

        if (!$excluded) {
            if ($this->isPrefetchDetected()) {
                $excluded = true;
                Common::printDebug("Prefetch request detected, not a real visit so we Ignore this visit/pageview");
            }
        }

        if ($excluded) {
            Common::printDebug("Visitor excluded.");
            return true;
        }

        return false;
    }

    protected function isPrefetchDetected()
    {
        return (isset($_SERVER["HTTP_X_PURPOSE"])
            && in_array($_SERVER["HTTP_X_PURPOSE"], array("preview", "instant")))
        || (isset($_SERVER['HTTP_X_MOZ'])
            && $_SERVER['HTTP_X_MOZ'] == "prefetch");
    }

    /**
     * Live/Bing/MSN bot and Googlebot are evolving to detect cloaked websites.
     * As a result, these sophisticated bots exhibit characteristics of
     * browsers (cookies enabled, executing JavaScript, etc).
     *
     * @see \DeviceDetector\Parser\Bot
     *
     * @return boolean
     */
    protected function isNonHumanBot()
    {
        $allowBots = $this->request->getParam('bots');

        $deviceDetector = DeviceDetectorFactory::getInstance($this->userAgent);

        return !$allowBots
        && ($deviceDetector->isBot()
            || IP::isIpInRange($this->ip, $this->getBotIpRanges()));
    }

    protected function getBotIpRanges()
    {
        return array(
            // Google
            '66.249.0.0/16',
            '64.233.172.0/24',
            // Live/Bing/MSN
            '64.4.0.0/18',
            '65.52.0.0/14',
            '157.54.0.0/15',
            '157.56.0.0/14',
            '157.60.0.0/16',
            '207.46.0.0/16',
            '207.68.128.0/18',
            '207.68.192.0/20',
            '131.253.26.0/20',
            '131.253.24.0/20',
            // Yahoo
            '72.30.198.0/20',
            '72.30.196.0/20',
            '98.137.207.0/20',
            // Chinese bot hammering websites
            '1.202.218.8'
        );
    }

    /**
     * Looks for the ignore cookie that users can set in the Piwik admin screen.
     * @return bool
     */
    protected function isIgnoreCookieFound()
    {
        if (IgnoreCookie::isIgnoreCookieFound()) {
            Common::printDebug('Piwik ignore cookie was found, visit not tracked.');
            return true;
        }

        return false;
    }

    /**
     * Checks if the visitor ip is in the excluded list
     *
     * @return bool
     */
    protected function isVisitorIpExcluded()
    {
        $websiteAttributes = Cache::getCacheWebsiteAttributes($this->idSite);

        if (!empty($websiteAttributes['excluded_ips'])) {
            if (IP::isIpInRange($this->ip, $websiteAttributes['excluded_ips'])) {
                Common::printDebug('Visitor IP ' . IP::N2P($this->ip) . ' is excluded from being tracked');
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the specified user agent should be excluded for the current site or not.
     *
     * Visits whose user agent string contains one of the excluded_user_agents strings for the
     * site being tracked (or one of the global strings) will be excluded.
     *
     * @internal param string $this ->userAgent The user agent string.
     * @return bool
     */
    protected function isUserAgentExcluded()
    {
        $websiteAttributes = Cache::getCacheWebsiteAttributes($this->idSite);

        if (!empty($websiteAttributes['excluded_user_agents'])) {
            foreach ($websiteAttributes['excluded_user_agents'] as $excludedUserAgent) {
                // if the excluded user agent string part is in this visit's user agent, this visit should be excluded
                if (stripos($this->userAgent, $excludedUserAgent) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns true if the Referrer is a known spammer.
     *
     * @return bool
     */
    protected function isReferrerSpamExcluded()
    {
        $spamHosts = Config::getInstance()->Tracker['referrer_urls_spam'];
        $spamHosts = explode(",", $spamHosts);

        $referrerUrl = $this->request->getParam('urlref');

        foreach($spamHosts as $spamHost) {
            if ( strpos($referrerUrl, $spamHost) !== false) {
                Common::printDebug('Referrer URL is a known spam: ' . $spamHost);
                return true;
            }
        }

        return false;
    }
}
