<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\Compliance;

use Piwik\Policy\CompliancePolicy;
use Piwik\Policy\PolicyEnforcementBypass;
use Piwik\Policy\PolicyManager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;

/**
 * Builds the granular per-setting compliance payload for a policy.
 */
class ComplianceSettingsProvider
{
    public const STATUS_COMPLIANT = 'compliant';
    public const STATUS_ENFORCED = 'enforced';
    public const STATUS_NON_COMPLIANT = 'non_compliant';
    public const STATUS_ON_BY_DEFAULT = 'on_by_default';
    public const STATUS_MANUAL = 'manual';

    public const SECTION_SETTINGS = 'settings';
    public const SECTION_EXTERNAL = 'external';

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @return array<string, mixed>
     */
    public function getPolicySettings(string $policyClass, ?int $idSite): array
    {
        $settings = [];
        $toggleableCount = 0;
        $allToggleablesEnforced = true;

        foreach (PolicyManager::getAllControlledSettings($policyClass, $idSite) as $settingClass) {
            if ($settingClass::isExternallyManagedByPolicyPage()) {
                continue;
            }

            $toggleableCount++;
            $enforced = $settingClass::isEnforced($idSite);
            $allToggleablesEnforced = $allToggleablesEnforced && $enforced;

            $settings[] = [
                'id' => $settingClass::getPolicySettingId(),
                'name' => $settingClass::getTitle(),
                'whatItDoes' => $settingClass::getWhatItDoes($idSite),
                'impact' => $settingClass::getImpact($idSite),
                'status' => $this->computeStatus($policyClass, $settingClass, $idSite, $enforced),
                'enforced' => $enforced,
                'toggleable' => true,
                'section' => self::SECTION_SETTINGS,
            ];
        }

        foreach ($this->getExternallyManagedSettings($policyClass, $idSite) as $externalSetting) {
            $settings[] = $externalSetting;
        }

        return [
            'policy' => $policyClass::getName(),
            'title' => $policyClass::getTitle(),
            'description' => $policyClass::getGranularDescription(),
            'configControlled' => PolicyManager::isPolicyConfigControlled($policyClass),
            'policyEnforced' => $toggleableCount > 0 && $allToggleablesEnforced,
            'settings' => $settings,
        ];
    }

    /**
     * Compares two payloads of {@link getPolicySettings()} and returns the settings whose
     * enforcement state or compliance status differs between them.
     *
     * Settings are matched on their stable identifier. One that is missing from either
     * payload is left out: the set of settings a policy controls only changes when plugins
     * are activated or deactivated, which is not a compliance change anyone performed here.
     *
     * @param array<string, mixed> $before payload taken before the settings were written
     * @param array<string, mixed> $after payload taken after the settings were written
     * @return array<int, array{id: string, name: string, enforced: bool|null, previousEnforced: bool|null, status: string, previousStatus: string}>
     */
    public function diffPolicySettings(array $before, array $after): array
    {
        $previousById = [];

        foreach ($before['settings'] ?? [] as $setting) {
            $previousById[$setting['id']] = $setting;
        }

        $changes = [];

        foreach ($after['settings'] ?? [] as $setting) {
            if (!array_key_exists($setting['id'], $previousById)) {
                continue;
            }

            $previous = $previousById[$setting['id']];

            if (
                $previous['enforced'] === $setting['enforced']
                && $previous['status'] === $setting['status']
            ) {
                continue;
            }

            $changes[] = [
                'id' => $setting['id'],
                'name' => $setting['name'],
                'enforced' => $setting['enforced'],
                'previousEnforced' => $previous['enforced'],
                'status' => $setting['status'],
                'previousStatus' => $previous['status'],
            ];
        }

        return $changes;
    }

    /**
     * @param class-string<CompliancePolicy> $policyClass
     * @return array<int, array<string, mixed>>
     */
    private function getExternallyManagedSettings(string $policyClass, ?int $idSite): array
    {
        $settings = [];

        foreach (PolicyManager::getAllControlledSettings($policyClass, $idSite) as $settingClass) {
            if (!$settingClass::isExternallyManagedByPolicyPage()) {
                continue;
            }

            // reflect the raw configuration: these settings are managed outside the
            // dashboard, so an active policy must not mask a non-compliant config
            $compliantOnItsOwn = PolicyEnforcementBypass::run(
                function () use ($settingClass, $policyClass, $idSite): bool {
                    return $settingClass::isCompliant($policyClass, $idSite);
                }
            );

            $settings[] = [
                'id' => $settingClass::getPolicySettingId(),
                'name' => $settingClass::getTitle(),
                'whatItDoes' => $settingClass::getWhatItDoes($idSite),
                'impact' => $settingClass::getImpact($idSite),
                'status' => $compliantOnItsOwn
                    ? self::STATUS_ON_BY_DEFAULT
                    : self::STATUS_NON_COMPLIANT,
                'enforced' => null,
                'toggleable' => false,
                'section' => self::SECTION_EXTERNAL,
            ];
        }

        foreach (PolicyManager::getAllUnknownSettings($policyClass) as $index => $unknownSetting) {
            $settings[] = [
                'id' => $policyClass::getName() . '.' . ($unknownSetting['id'] ?? 'unknown' . $index),
                'name' => $unknownSetting['title'],
                'whatItDoes' => $unknownSetting['note'],
                'impact' => $unknownSetting['impact'] ?? '',
                'status' => self::STATUS_MANUAL,
                'enforced' => null,
                'toggleable' => false,
                'section' => self::SECTION_EXTERNAL,
            ];
        }

        return $settings;
    }

    /**
     * A setting whose requirement is met reports "enforced" whenever its enforcement
     * toggle is on — even when the underlying configuration would satisfy the
     * requirement on its own — and "compliant" otherwise. A setting whose requirement
     * isn't met reports "non_compliant" regardless of the toggle (e.g. the IP address
     * mask length while IP anonymisation itself is disabled).
     *
     * @param class-string<CompliancePolicy> $policyClass
     * @param class-string<PolicyComparisonInterface<mixed>> $settingClass
     */
    private function computeStatus(string $policyClass, string $settingClass, ?int $idSite, bool $enforced): string
    {
        if (!$settingClass::isCompliant($policyClass, $idSite)) {
            return self::STATUS_NON_COMPLIANT;
        }

        return $enforced ? self::STATUS_ENFORCED : self::STATUS_COMPLIANT;
    }
}
