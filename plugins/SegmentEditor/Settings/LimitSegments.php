<?php

namespace Piwik\Plugins\SegmentEditor\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\CompliancePolicyEnforcedSetting;
use Piwik\Policy\CnilPolicy;

class LimitSegments extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate("SegmentEditor_LimitSegmentsSettingTitle");
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate("SegmentEditor_LimitSegmentsSettingRequirementNote");
    }
}
