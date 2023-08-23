<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Option;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Tracker\Request;

class JsTrackerInstallCheck extends \Piwik\Plugin
{
    const QUERY_PARAM_NAME = 'tracker_install_check';
    const OPTION_NAME_PREFIX = 'JsTrackerInstallCheck_';
    const MAX_NONCE_AGE_SECONDS = 30;

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

        // If the request has already been excluded, we don't want to override that
        $excluded = true;
        StaticContainer::get(LoggerInterface::class)->debug('Excluding visit as JS tracker install test.');

        $idSite = $request->getIdSite();
        $nonceOptionString = Option::get(self::OPTION_NAME_PREFIX . $idSite);
        if (empty($nonceOptionString)) {
            return;
        }

        $nonceOptionArray = json_decode($nonceOptionString, true);
        if (empty($nonceOptionArray)) {
            return;
        }

        // Make sure that the nonce matches
        if (empty($nonceOptionArray['nonce']) || $nonceOptionArray['nonce'] !== $trackerInstallCheckParam) {
            return;
        }

        // If the nonce is older 30 seconds, ignore it. This should be plenty of time because an API call creates the nonce just before opening the site
        if (empty($nonceOptionArray['time']) || Date::getNowTimestamp() - $nonceOptionArray['time'] > self::MAX_NONCE_AGE_SECONDS) {
            return;
        }

        // Since the nonce matches and hasn't expired, update the option indicating success
        $nonceOptionArray['isSuccessful'] = true;
        Option::set(self::OPTION_NAME_PREFIX . $idSite, json_encode($nonceOptionArray));
    }

    /**
     * Check whether a test request has been recorded for the provided nonce. If no nonce is provided, the recorded
     * result for the site will be returned.
     *
     * @param string $idSite
     * @param string $nonce The unique nonce used to identify the test requests. Optionally can be left empty if simply
     * wanting to check if the site has been successfully tested.
     * @return bool Indicating whether the nonce check was marked as successful or not
     */
    public function checkForJsTrackerInstallTestSuccess(string $idSite, string $nonce = ''): bool
    {
        $nonceOptionString = Option::get(self::OPTION_NAME_PREFIX . $idSite);
        if (empty($nonceOptionString)) {
            return false;
        }

        $nonceOptionArray = json_decode($nonceOptionString, true);
        if (empty($nonceOptionArray)) {
            return false;
        }

        // Check if the nonce matches the recorded nonce
        if (!empty($nonce) && (empty($nonceOptionArray['nonce']) || $nonceOptionArray['nonce'] !== $nonce)) {
            return false;
        }

        return !empty($nonceOptionArray['isSuccessful']);
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
        // Check if a nonce already exists from the past few seconds and reuse it if possible
        $optionName = self::OPTION_NAME_PREFIX . $idSite;
        $nonceOptionString = Option::get($optionName);
        if (!empty($nonceOptionString)) {
            $nonceOptionArray = json_decode($nonceOptionString, true);
        }

        $nonceString = md5(SettingsPiwik::getSalt() . time() . Common::generateUniqId());
        // If a nonce already exists, and it's less than 15 seconds under the max age, return that.
        if (!empty($nonceOptionArray['nonce']) && !empty($nonceOptionArray['time'])
            && Date::getNowTimestamp() - $nonceOptionArray['time'] < (self::MAX_NONCE_AGE_SECONDS - 15)) {
            $nonceString = $nonceOptionArray['nonce'];
        }
        Option::set($optionName, json_encode([
            'nonce' => $nonceString,
            'time' => Date::getNowTimestamp(),
            'isSuccessful' => false
        ]));

        // If the URL wasn't provided or isn't a valid URL, use the main URL configured for the site
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $url = Site::getMainUrlFor($idSite);
        }
        $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . self::QUERY_PARAM_NAME . '=' . $nonceString;

        return ['url' => $url, 'nonce' => $nonceString];
    }
}
