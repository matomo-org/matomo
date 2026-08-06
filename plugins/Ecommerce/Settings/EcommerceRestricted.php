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

class EcommerceRestricted extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate('Ecommerce_EcommercePolicySettingTitle');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('Ecommerce_EcommercePolicyComplianceDescription');
    }

    public static function getComplianceImpactNote(?int $idSite = null): string
    {
        $idSites = self::getIdSitesToCheck($idSite);

        if (!self::hasEcommerceEnabledSite($idSites)) {
            if ($idSite !== null && count($idSites) === 1) {
                return Piwik::translate('Ecommerce_EcommercePolicyComplianceImpactNoEcommerceSingle');
            }

            return Piwik::translate('Ecommerce_EcommercePolicyComplianceImpactNoEcommerceAll');
        }

        return Piwik::translate('Ecommerce_EcommercePolicyComplianceImpact');
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
}
