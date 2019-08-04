<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager\Tracker;

use Piwik\Common;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\SettingsPiwik;
use Piwik\Tracker\Request;
use Piwik\Tracker;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;

class RequestProcessor extends Tracker\RequestProcessor
{
    public function manipulateRequest(Request $request)
    {
        $privacyConfig = new PrivacyManagerConfig();

        if ($privacyConfig->anonymizeUserId) {
            $userId = $request->getParam('uid');
            if ($this->isValueSet($userId)) {
                $userIdAnonymized = self::anonymizeUserId($userId);
                $request->setParam('uid', $userIdAnonymized);
            }
        }

        if ($privacyConfig->anonymizeOrderId) {
            $orderId = $request->getParam('ec_id');
            if ($this->isValueSet($orderId)) {
                $orderIdAnonymized = sha1(Common::getRandomInt() . $orderId . time() . SettingsPiwik::getSalt());
                $request->setParam('ec_id', $orderIdAnonymized);
            }
        }
    }

    /**
     * pseudo anonymization as we need to make sure to always generate the same UserId for the same original UserID
     *
     * @param $userId
     * @return string
     */
    public static function anonymizeUserId($userId)
    {
        $trackerCache = Tracker\Cache::getCacheGeneral();
        $salt = '';
        if (!empty($trackerCache[PrivacyManager::OPTION_USERID_SALT])) {
            $salt = $trackerCache[PrivacyManager::OPTION_USERID_SALT];
        }
        if(empty($salt)) {
            return $userId;
        }
        return sha1($userId . $salt);
    }

    private function isValueSet($value)
    {
        return $value !== '' && $value !== false && $value !== null;
    }
}
