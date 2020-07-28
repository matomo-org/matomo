<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager\Tracker;

use Piwik\Common;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\SettingsPiwik;
use Piwik\Tracker\Request;
use Piwik\Tracker;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Tracker\Visit\VisitProperties;

class RequestProcessor extends Tracker\RequestProcessor
{
    private $config;
    private $referrerAnonymizer;

    public function __construct(PrivacyManagerConfig $config, ReferrerAnonymizer $referrerAnonymizer)
    {
        $this->config = $config;
        $this->referrerAnonymizer = $referrerAnonymizer;
    }

    public function manipulateRequest(Request $request)
    {
        if ($this->config->anonymizeUserId) {
            $userId = $request->getParam('uid');
            if ($this->isValueSet($userId)) {
                $userIdAnonymized = self::anonymizeUserId($userId);
                $request->setParam('uid', $userIdAnonymized);
            }
        }

        if ($this->config->anonymizeOrderId) {
            $orderId = $request->getParam('ec_id');
            if ($this->isValueSet($orderId)) {
                $orderIdAnonymized = sha1(Common::getRandomInt() . $orderId . time() . SettingsPiwik::getSalt());
                $request->setParam('ec_id', $orderIdAnonymized);
            }
        }

        if ($this->config->anonymizeReferrer === ReferrerAnonymizer::EXCLUDE_ALL) {
            $request->setParam('urlref', '');
        }
    }

    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        $url = $visitProperties->getProperty('referer_url');
        $url = $this->referrerAnonymizer->anonymiseReferrerUrl($url, $this->config->anonymizeReferrer);
        $visitProperties->setProperty('referer_url', $url);
    }

    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        if (isset($valuesToUpdate['referer_url'])) {
            $valuesToUpdate['referer_url'] = $this->referrerAnonymizer->anonymiseReferrerUrl($valuesToUpdate['referer_url'], $this->config->anonymizeReferrer);
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
