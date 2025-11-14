<?php

namespace Piwik\Plugins\Resolution\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\CompliancePolicyEnforcedSetting;
use Piwik\Policy\CnilPolicy;

class ScreenResolutionDetectionDisabled extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate('Resolution_ScreenResolutionDetectionDisabled');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('Resolution_ScreenResolutionDetectionDisabledRequirementNote');
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
    }
}
