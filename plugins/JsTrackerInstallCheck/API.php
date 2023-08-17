<?php
/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */
namespace Piwik\Plugins\JsTrackerInstallCheck;

use Piwik\Common;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\SettingsPiwik;

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
     * @throws \Exception
     */
    public function initiateJsTrackerInstallTest(string $idSite): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        $nonceString = md5(SettingsPiwik::getSalt() . time() . Common::generateUniqId());
        Option::set(JsTrackerInstallCheck::OPTION_NAME_PREFIX . $idSite, json_encode([
            'nonce' => $nonceString,
            'time' => Date::getNowTimestamp(),
            'isSuccessful' => false
        ]));

        // Look up the site so that we can get the main URL
        $site = SitesManagerApi::getInstance()->getSiteFromId($idSite);

        $url = $site['main_url'];
        $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $nonceString;

        return ['url' => $url, 'nonce' => $nonceString];
    }
}
