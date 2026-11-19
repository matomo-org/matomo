<?php

namespace Piwik\Plugins\Resolution\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\CompliancePolicyEnforcedSetting;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Policy\CnilPolicy;

class ScreenResolutionDetectionDisabled extends CompliancePolicyEnforcedSetting
{
    public static function getTitle(): string
    {
        return Piwik::translate('Resolution_ScreenResolutionDetectionDisabled');
    }

    public static function getWhatItDoes(?int $idSite = null): string
    {
        // the consent-aware wording describes what a site measuring consent-aware analytics
        // does. It is off by default, and a site that has not opted in treats every visitor the
        // same, so the plain wording is what is true there
        if (null !== $idSite && ConsentAwareAnalytics::isEnabledForSite($idSite)) {
            return Piwik::translate('Resolution_ScreenResolutionDetectionDisabledWhatItDoesConsentAware');
        }

        return Piwik::translate('Resolution_ScreenResolutionDetectionDisabledWhatItDoes');
    }

    public static function getImpact(?int $idSite = null): string
    {
        // the consent-aware wording describes what a site measuring consent-aware analytics
        // does. It is off by default, and a site that has not opted in treats every visitor the
        // same, so the plain wording is what is true there
        if (null !== $idSite && ConsentAwareAnalytics::isEnabledForSite($idSite)) {
            return Piwik::translate('Resolution_ScreenResolutionDetectionDisabledImpactConsentAware');
        }

        return Piwik::translate('Resolution_ScreenResolutionDetectionDisabledImpact');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('Resolution_ScreenResolutionDetectionDisabledRequirementNote');
    }

    public static function getPolicyOrder(): int
    {
        return 30;
    }

    public static function getPolicyRequirements(): array
    {
        return [
            CnilPolicy::class => true,
        ];
    }
}
