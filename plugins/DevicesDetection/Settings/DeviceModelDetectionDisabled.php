<?php

namespace Piwik\Plugins\DevicesDetection\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\CompliancePolicyEnforcedSetting;
use Piwik\Policy\CnilPolicy;

class DeviceModelDetectionDisabled extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabled');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabledRequirementNote');
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
    }
}
