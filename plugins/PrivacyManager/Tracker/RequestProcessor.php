<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
use Piwik\Tracker\Visitor;
use Piwik\Plugins\CoreHome\Columns\Consent;

class RequestProcessor extends Tracker\RequestProcessor
{
    private $config;
    private $referrerAnonymizer;

    public function __construct(PrivacyManagerConfig $config, ReferrerAnonymizer $referrerAnonymizer)
    {
        $this->config = $config;
        $this->referrerAnonymizer = $referrerAnonymizer;
    }

    private function provideIdSiteToPrivacyManagerConfig(Request $request): void
    {
        $idSite = $request->getIdSiteIfExists();
        if (is_numeric($idSite) && $idSite) {
            $this->config->setIdSite((int) $idSite);
        }
    }

    /**
     * Resolves how far the referrer of this request needs to be anonymised.
     *
     * Consent only lifts what a compliance policy imposed. The measure the admin configured
     * themselves still applies, so a consented request is anonymised down to that level rather
     * than to the stricter level a policy may be enforcing on top of it.
     */
    /**
     * The level this request's referrer is anonymised to.
     *
     * Consent lifts the level a compliance policy enforces back to the one the admin configured,
     * and that consent belongs to the visit rather than to the request: a later request in a
     * visit that consented is not anonymised more strictly just because it does not repeat the
     * flag. Where the visit is not known - a request handled before one is - the request's own
     * answer is all there is.
     */
    private function getReferrerAnonymisation(Request $request, ?VisitProperties $visitProperties = null): string
    {
        $hasConsent = null === $visitProperties
            ? $request->hasConsent()
            : Consent::visitHasConsent($request, Visitor::makeFromVisitProperties($visitProperties, $request));

        if (!$hasConsent) {
            return $this->config->anonymizeReferrer;
        }

        return (string) $this->config->getConfiguredValue('anonymizeReferrer');
    }

    public function manipulateRequest(Request $request)
    {
        $this->provideIdSiteToPrivacyManagerConfig($request);

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
    }

    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        $this->provideIdSiteToPrivacyManagerConfig($request);
        $anonymisation = $this->getReferrerAnonymisation($request, $visitProperties);

        $type = $visitProperties->getProperty('referer_type');

        // we do not anonymise the referrer url in manipulateRequest because otherwise the referrer would not be detected
        // correctly
        $url = $visitProperties->getProperty('referer_url');
        $url = $this->referrerAnonymizer->anonymiseReferrerUrl($url, $anonymisation);
        $visitProperties->setProperty('referer_url', $url);

        $name = $visitProperties->getProperty('referer_name');
        $name = $this->referrerAnonymizer->anonymiseReferrerName($name, $type, $anonymisation);
        $visitProperties->setProperty('referer_name', $name);

        $keyword = $visitProperties->getProperty('referer_keyword');
        $keyword = $this->referrerAnonymizer->anonymiseReferrerKeyword($keyword, $type, $anonymisation);
        $visitProperties->setProperty('referer_keyword', $keyword);
    }

    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        $this->provideIdSiteToPrivacyManagerConfig($request);
        $anonymisation = $this->getReferrerAnonymisation($request, $visitProperties);

        if (isset($valuesToUpdate['referer_type'])) {
            $type = $valuesToUpdate['referer_type'];
        } else {
            $type = $visitProperties->getProperty('referer_type');
        }

        if (isset($valuesToUpdate['referer_url'])) {
            $valuesToUpdate['referer_url'] = $this->referrerAnonymizer->anonymiseReferrerUrl($valuesToUpdate['referer_url'], $anonymisation);
        }

        if (isset($valuesToUpdate['referer_name'])) {
            $valuesToUpdate['referer_name'] = $this->referrerAnonymizer->anonymiseReferrerName($valuesToUpdate['referer_name'], $type, $anonymisation);
        }

        if (isset($valuesToUpdate['referer_keyword'])) {
            $valuesToUpdate['referer_keyword'] = $this->referrerAnonymizer->anonymiseReferrerKeyword($valuesToUpdate['referer_keyword'], $type, $anonymisation);
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
        if (empty($salt)) {
            return $userId;
        }
        return sha1($userId . $salt);
    }

    private function isValueSet($value)
    {
        return $value !== '' && $value !== false && $value !== null;
    }
}
