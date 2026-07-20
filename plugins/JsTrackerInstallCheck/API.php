<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck;

use Piwik\Piwik;
use Piwik\Site;
use Piwik\UrlHelper;

/**
 * @internal
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var JsTrackerInstallCheck
     */
    protected $jsTrackerInstallCheck;

    // disables automatic sanitizing for all public API methods in this class
    protected $autoSanitizeInputParams = false;

    public function __construct(JsTrackerInstallCheck $jsTrackerInstallCheck)
    {
        $this->jsTrackerInstallCheck = $jsTrackerInstallCheck;
    }

    /**
     * Check whether a test request has been recorded for the provided nonce. If no request has been recorded or the
     * nonce isn't found, return false. This also returns the main URL for the specified site so that we can auto-
     * populate an input with it.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @param string $nonce Optional nonce string. If provided, it validates whether the success response matches the
     * provided nonce. If omitted, it simply returns the most recent result for the provided site.
     * @return array{isSuccess:bool, mainUrl:string} Indicates whether the check was successful and provides the site's main URL.
     */
    public function wasJsTrackerInstallTestSuccessful(int $idSite, string $nonce = ''): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        if (!empty($nonce) && !\preg_match('/^[a-f0-9]{32}$/i', $nonce)) {
            throw new \Exception('The provided nonce is invalid.');
        }

        return [
            'isSuccess' => $this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($idSite, $nonce),
            'mainUrl' => Site::getMainUrlFor($idSite),
        ];
    }

    /**
     * Initiate a test whether the JS tracking code has been successfully installed for a site. It generates a nonce and
     * stores it in the option table so that it can be accessed later during the Tracker.isExcludedVisit event.
     *
     * @param int $idSite The numeric ID of the website to test.
     * @param string $url Optional URL to append the nonce to. If not provided, the site's main URL is used.
     * @return array{url:string, nonce:string} The test URL and generated nonce.
     */
    public function initiateJsTrackerInstallTest(int $idSite, string $url = ''): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        if (!empty($url) && !UrlHelper::isLookLikeUrl($url)) {
            throw new \Exception(Piwik::translate('SitesManager_ExceptionInvalidUrl', $url));
        }

        return $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($idSite, $url);
    }
}
