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

use Piwik\Option;
use Piwik\Piwik;

class API extends \Piwik\Plugin\API
{
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

        $nonceOptionString = Option::get(JsTrackerInstallCheck::OPTION_NAME_PREFIX . $idSite);
        if (empty($nonceOptionString)) {
            return ['isSuccess' => false];
        }

        $nonceOptionArray = json_decode($nonceOptionString, true);
        if (empty($nonceOptionArray)) {
            return ['isSuccess' => false];
        }

        return ['isSuccess' => !empty($nonceOptionArray['isSuccessful'])];
    }
}
