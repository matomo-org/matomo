<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\CompliancePolicyEnforcedSetting;
use Piwik\Policy\CnilPolicy;
use Piwik\Site;
use Piwik\Url;

class EcommerceRestricted extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate('Ecommerce_EcommercePolicySettingTitle');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        $idSites = self::getIdSitesToCheck($idSite);

        if (!self::hasEcommerceEnabledSite($idSites)) {
            return self::getCompliantMessage($idSite, $idSites);
        }

        if (self::getInstance($idSite)->getValue() === false) {
            return Piwik::translate('Ecommerce_EcommercePolicySettingNonCompliantNote');
        }

        $manageUrl = 'index.php' . Url::getCurrentQueryStringWithParametersModified(self::getManageParams($idSite));

        return Piwik::translate('Ecommerce_EcommercePolicySettingRequirementNote', ['<a href="' . $manageUrl . '">', '</a>']);
    }

    private static function getManageParams(?int $idSite): array
    {
        $params = [
            'module' => 'SitesManager',
            'action' => 'index',
        ];

        if ($idSite !== null) {
            $params['idSite'] = $idSite;
        }

        return $params;
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        $policyValues = static::getPolicyRequirements();
        if (!array_key_exists($policy, $policyValues)) {
            return true;
        }

        $currentValue = self::getInstance($idSite)->getValue();

        $idSites = self::getIdSitesToCheck($idSite);

        return $currentValue === $policyValues[$policy] || !self::hasEcommerceEnabledSite($idSites);
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
    }

    private static function getIdSitesToCheck(?int $idSite): array
    {
        if ($idSite === null) {
            return Site::getIdSitesFromIdSitesString('all');
        }

        $ids = Site::getIdSitesFromIdSitesString((string) $idSite);

        return empty($ids) ? [$idSite] : $ids;
    }

    private static function hasEcommerceEnabledSite(array $idSites): bool
    {
        foreach ($idSites as $siteId) {
            if (Site::isEcommerceEnabledFor((int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    private static function getCompliantMessage(?int $idSite, array $idSites): string
    {
        if ($idSite !== null && count($idSites) === 1) {
            return Piwik::translate('Ecommerce_EcommercePolicySettingCompliantSingle');
        }

        return Piwik::translate('Ecommerce_EcommercePolicySettingCompliantAll');
    }
}
