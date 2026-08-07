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

    public static function getComplianceTitle(?int $idSite = null): string
    {
        return Piwik::translate('Ecommerce_EcommercePolicyComplianceTitle');
    }

    public static function getWhatItDoes(?int $idSite = null): string
    {
        $keys = self::getComplianceStateTranslationKeys($idSite);

        // The closing paragraph is identical in all three states, so it lives in its own key rather
        // than being repeated in each. Both keys are complete, standalone paragraphs, so the block
        // separator belongs here and not inside a translation where it could be dropped per locale.
        return Piwik::translate($keys['description'])
            . '<br /><br />'
            . Piwik::translate('Ecommerce_EcommercePolicyComplianceDescriptionRationale');
    }

    public static function getImpact(?int $idSite = null): string
    {
        return Piwik::translate(self::getComplianceStateTranslationKeys($idSite)['impact']);
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

    /**
     * Resolves the description and impact keys for the current ecommerce state.
     *
     * Both compliance columns describe the same three states, so they take their keys from this one
     * place and cannot end up describing different states within the same row.
     *
     * A non-null $idSite always resolves to exactly one site, so it alone distinguishes the
     * single-site wording from the all-websites wording.
     *
     * @return array{description: string, impact: string}
     */
    private static function getComplianceStateTranslationKeys(?int $idSite): array
    {
        if (self::hasEcommerceEnabledSite(self::getIdSitesToCheck($idSite))) {
            return [
                'description' => 'Ecommerce_EcommercePolicyComplianceDescription',
                'impact' => 'Ecommerce_EcommercePolicyComplianceImpact',
            ];
        }

        if ($idSite !== null) {
            return [
                'description' => 'Ecommerce_EcommercePolicyComplianceDescriptionNoEcommerceSingle',
                'impact' => 'Ecommerce_EcommercePolicyComplianceImpactNoEcommerceSingle',
            ];
        }

        return [
            'description' => 'Ecommerce_EcommercePolicyComplianceDescriptionNoEcommerceAll',
            'impact' => 'Ecommerce_EcommercePolicyComplianceImpactNoEcommerceAll',
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
