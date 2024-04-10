<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck;

use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\JsTrackerInstallCheck\NonceOption\JsTrackerInstallCheckOption;
use Piwik\Site;
use Piwik\Tracker\Request;

class JsTrackerInstallCheck extends \Piwik\Plugin
{
    const QUERY_PARAM_NAME = 'tracker_install_check';

    public function registerEvents()
    {
        return [
            'Tracker.isExcludedVisit' => 'isExcludedVisit',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
        ];
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'JsTrackerInstallCheck_TestInstallationDescription';
        $translationKeys[] = 'JsTrackerInstallCheck_TestInstallationBtnText';
        $translationKeys[] = 'JsTrackerInstallCheck_JsTrackingCodeInstallCheckSuccessMessage';
        $translationKeys[] = 'JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessage';
        $translationKeys[] = 'General_Testing';
        $translationKeys[] = 'JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessageWordpress';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.less";
    }

    public function isExcludedVisit(&$excluded, Request $request)
    {
        // We don't have an early return when the request has already been excluded because we want the test to work even if the request is excluded due to a VPN or something

        $hasInstallCheckParam = $request->hasParam(self::QUERY_PARAM_NAME);
        if (!$hasInstallCheckParam) {
            return;
        }

        $trackerInstallCheckParam = $request->getParams()[self::QUERY_PARAM_NAME];
        if (empty($trackerInstallCheckParam)) {
            return;
        }

        // Make sure that the request is marked as excluded if it isn't already
        $excluded = true;
        StaticContainer::get(LoggerInterface::class)->debug('Excluding visit as JS tracker install test.');

        // If the nonce exists and isn't expired, update it to indicate success
        StaticContainer::get(JsTrackerInstallCheckOption::class)->markNonceAsSuccessFul($request->getIdSite(), $trackerInstallCheckParam);
    }

    /**
     * Check whether a test request has been recorded for the provided nonce. If no nonce is provided, the recorded
     * result for the site will be returned. This is determined by whether there's only one previous nonce or if the URL
     * matches the main URL of the site.
     *
     * @param int $idSite
     * @param string $nonce The unique nonce used to identify the test requests. Optionally can be left empty if simply
     * wanting to check if the site has been successfully tested.
     * @return bool Indicating whether the nonce check was marked as successful
     */
    public function checkForJsTrackerInstallTestSuccess(int $idSite, string $nonce = ''): bool
    {
        $optionHelper = StaticContainer::get(JsTrackerInstallCheckOption::class);
        // If the nonce was provided, filter out the expired nonces
        $nonceMap = !empty($nonce) ? $optionHelper->getCurrentNonceMap($idSite) : $optionHelper->getNonceMap($idSite);
        if (!empty($nonce)) {
            return !empty($nonceMap[$nonce]['isSuccessful']);
        }

        if (empty($nonceMap)) {
            return false;
        }

        // If there's only one nonce for the site, just use that result
        if (count($nonceMap) === 1) {
            return array_values($nonceMap)[0][JsTrackerInstallCheckOption::NONCE_DATA_IS_SUCCESS];
        }

        // Since there's more than one nonce, let's see if one of them matches the main URL of the site
        $mainUrl = Site::getMainUrlFor($idSite);
        foreach ($nonceMap as $nonceData) {
            if (
                !empty($mainUrl) && !empty($nonceData[JsTrackerInstallCheckOption::NONCE_DATA_URL])
                && $mainUrl === $nonceData[JsTrackerInstallCheckOption::NONCE_DATA_URL]
            ) {
                return !empty($nonceData['isSuccessful']);
            }
        }

        return false;
    }

    /**
     * Initiate a test whether the JS tracking code has been successfully installed for a site. It generates a nonce and
     * stores it in the option table so that it can be accessed later during the Tracker.isExcludedVisit event.
     *
     * @param int $idSite
     * @param string $url Optional URL to append the nonce to. If not provided, it uses the main URL of the site
     * @return array containing the URL constructed using the main URL for the site and the newly created nonce as a
     * query parameter.
     * E.g ['url' => 'https://some-site.com?tracker_install_check=c3dfa1abbbab6381baca0793b8dd5d', 'nonce' => 'c3dfa1abbbab6381baca0793b8dd5d']
     * @throws \Exception
     */
    public function initiateJsTrackerInstallTest(int $idSite, string $url = ''): array
    {
        // If the URL wasn't provided or isn't a valid URL, use the main URL configured for the site
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $url = Site::getMainUrlFor($idSite);
        }

        $nonceString = StaticContainer::get(JsTrackerInstallCheckOption::class)->createNewNonce($idSite, $url);

        $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . self::QUERY_PARAM_NAME . '=' . $nonceString;

        return ['url' => $url, 'nonce' => $nonceString];
    }
}
