<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\JsTrackerInstallCheck;

use Piwik\Piwik;

class API extends \Piwik\Plugin\API
{
    /**
     * @var JsTrackerInstallCheck
     */
    protected $jsTrackerInstallCheck;

    public function __construct(JsTrackerInstallCheck $jsTrackerInstallCheck)
    {
        $this->jsTrackerInstallCheck = $jsTrackerInstallCheck;
    }

    /**
     * Check whether a test request has been recorded for the provided nonce
     *
     * @param string $idSite
     * @param string $nonce
     * @return array list of containers ['isSuccess' => true]
     * @throws \Exception If the user doesn't have the right permissions
     */
    public function checkForJsTrackerInstallTestSuccess(string $idSite, string $nonce): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        return $this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($idSite, $nonce);
    }

    /**
     * Check whether a test request has been recorded for the provided site
     *
     * @param string $idSite
     * @return array list of containers ['isSuccess' => true]
     * @throws \Exception If the user doesn't have the right permissions
     */
    public function getJsTrackerInstallTestResult(string $idSite): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        return $this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($idSite);
    }

    /**
     * Initiate a test whether the JS tracking code has been successfully installed for a site. It generates a nonce and
     * stores it in the option table so that it can be accessed later during the Tracker.isExcludedVisit event.
     *
     * @param string $idSite
     * @return array containing the URL constructed using the main URL for the site and the newly created nonce as a
     * query parameter.
     * E.g ['url' => 'https://some-site.com?tracker_install_check=c3dfa1abbbab6381baca0793b8dd5d', 'nonce' => 'c3dfa1abbbab6381baca0793b8dd5d']
     * @throws \Exception If the user doesn't have the right permissions
     */
    public function initiateJsTrackerInstallTest(string $idSite): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        return $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($idSite);
    }
}
