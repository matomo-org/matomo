<?php

namespace Piwik\Plugins\DevicesDetection\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Plugins\PrivacyManager\Settings\CompliancePolicyEnforcedSetting;
use Piwik\Policy\CnilPolicy;

class DeviceModelDetectionDisabled extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabled');
    }

    public static function getWhatItDoes(?int $idSite = null): string
    {
        // the consent-aware wording describes what a site measuring consent-aware analytics
        // does. It is off by default, and a site that has not opted in treats every visitor the
        // same, so the plain wording is what is true there
        if (null !== $idSite && ConsentAwareAnalytics::isEnabledForSite($idSite)) {
            return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabledWhatItDoes2');
        }

        return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabledWhatItDoes');
    }

    public static function getImpact(?int $idSite = null): string
    {
        // the consent-aware wording describes what a site measuring consent-aware analytics
        // does. It is off by default, and a site that has not opted in treats every visitor the
        // same, so the plain wording is what is true there
        if (null !== $idSite && ConsentAwareAnalytics::isEnabledForSite($idSite)) {
            return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabledImpact2');
        }

        return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabledImpact');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('DevicesDetection_DeviceModelDetectionDisabledRequirementNote');
    }

    public static function getPolicyOrder(): int
    {
        return 25;
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
    }
}
