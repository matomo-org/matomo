<?php

namespace Piwik\Plugins\WebsiteMeasurable\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\CompliancePolicyEnforcedSetting;
use Piwik\Policy\CnilPolicy;

class EcommerceRestricted extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate('Goals_Ecommerce');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('WebsiteMeasurable_EcommercePolicySettingRequirementNote');
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
    }
}
