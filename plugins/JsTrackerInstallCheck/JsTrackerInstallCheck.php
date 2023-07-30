<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Tracker\Request;
use Psr\Log\LoggerInterface;

class JsTrackerInstallCheck extends \Piwik\Plugin
{
    const QUERY_PARAM_NAME = 'tracker_install_check';
    const NONCE_NAME = 'JsTrackerInstallCheck.checkForJsTrackerInstallTestSuccess';
    const OPTION_NAME_PREFIX = 'JsTrackerInstallCheck_';

    public function registerEvents()
    {
        return [
            'Tracker.isExcludedVisit' => 'isExcludedVisit',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function isExcludedVisit(&$excluded, Request $request)
    {
        if ($excluded) {
            return; // already excluded, not needed to check
        }

        $hasInstallCheckParam = $request->hasParam(self::QUERY_PARAM_NAME);
        if (!$hasInstallCheckParam) {
            return;
        }

        $trackerInstallCheckParam = $request->getParams()[self::QUERY_PARAM_NAME];
        if (empty($trackerInstallCheckParam)) {
            return;
        }

        StaticContainer::get(LoggerInterface::class)->debug('Excluding visit as JS tracker install test.');
        $excluded = 'excluded: testing whether JS tracker is installed';

        $nonceOptionString = Option::get(self::OPTION_NAME_PREFIX . $request->getIdSite());
        if (empty($nonceOptionString)) {
            return;
        }

        $nonceOptionArray = json_decode($nonceOptionString, true);
        if (empty($nonceOptionArray)) {
            return;
        }

        if (empty($nonceOptionArray['nonce']) || $nonceOptionArray['nonce'] !== $trackerInstallCheckParam) {
            return;
        }

        // If the nonce is older than 5 minutes (300 seconds), ignore it
        if (empty($nonceOptionArray['time']) || Date::getNowTimestamp() - $nonceOptionArray['time'] > 300) {
            return;
        }

        // Since the nonce check didn't throw an error, that means it passed. Set the option indicating success
        $nonceOptionArray['isSuccessful'] = true;
        Option::set(self::OPTION_NAME_PREFIX . $request->getIdSite(), json_encode($nonceOptionArray));
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'JsTrackerInstallCheck_EnterSiteUrl';
        $translationKeys[] = 'JsTrackerInstallCheck_StartTestBtnText';
        $translationKeys[] = 'JsTrackerInstallCheck_TestHelpText';
        $translationKeys[] = 'JsTrackerInstallCheck_TestSuccessMessage';
        $translationKeys[] = 'JsTrackerInstallCheck_TestFailureMessage';
    }
}
