<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy;

use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\ConfigSettingInterface;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\SystemSettingInterface;
use Piwik\Settings\Interfaces\Traits\Getters\ConfigGetterTrait;
use Piwik\Settings\Interfaces\Traits\Setters\MeasurableSetterTrait;
use Piwik\Settings\Interfaces\Traits\Setters\SystemSetterTrait;

/**
 * @implements SystemSettingInterface<bool>
 * @implements MeasurableSettingInterface<bool>
 */
abstract class CompliancePolicy implements SystemSettingInterface, MeasurableSettingInterface, ConfigSettingInterface
{
    /**
     * @use SystemSetterTrait<bool>
     */
    use SystemSetterTrait;

    /**
     * @use MeasurableSetterTrait<bool>
     */
    use MeasurableSetterTrait;

    /**
     * @use ConfigGetterTrait<bool>
     */
    use ConfigGetterTrait;

    abstract public static function getName(): string;
    abstract public static function getTitle(): string;
    abstract protected static function generateDescription(): string;
    abstract protected static function generateWarnings(): string;

    public static function getDescription(): string
    {
        return self::decorateDescription(static::generateDescription());
    }

    /**
     * Returns the description shown above the granular per-setting compliance table.
     *
     * Policies without dedicated copy for the granular table fall back to the description
     * returned by {@link getDescription()}.
     *
     * @internal Only needed while the granular table and the legacy compliance table coexist.
     */
    public static function getGranularDescription(): string
    {
        return self::decorateDescription(static::generateGranularDescription());
    }

    protected static function generateGranularDescription(): string
    {
        return static::generateDescription();
    }

    /**
     * Returns the status legend of the granular per-setting table as list markup.
     *
     * The list is built here rather than in the translations so that translators cannot
     * break the markup or the class the bullets depend on.
     */
    protected static function getGranularStatusLegend(): string
    {
        $items = [
            'General_ComplianceStatusLegendCompliant',
            'General_ComplianceStatusLegendCompliantEnforced',
            'General_ComplianceStatusLegendNonCompliant',
            'General_ComplianceStatusLegendManual',
        ];

        $legend = '';
        foreach ($items as $item) {
            $legend .= '<li>' . Piwik::translate($item) . '</li>';
        }

        return "<ul class='browser-default'>" . $legend . '</ul>';
    }

    private static function decorateDescription(string $description): string
    {
        /**
         * This event is triggered while the description of a compliance policy is
         * being generated. The policy description can be modified via this event.
         *
         * @param string &$description of the policy.
         */
        Piwik::postEvent('CompliancePolicy.updatePolicyDescription', [&$description, static::class]);

        $shouldShowWarnings = true;

        /**
         * This event is triggered while the description of a compliance policy is
         * being generated, and controls whether any warnings specific to the policy
         * are displayed at the end of the description.
         *
         * @param bool &$shouldShowWarnings set to false if the warnings should be hidden
         */
        Piwik::postEvent('CompliancePolicy.shouldShowWarnings', [&$shouldShowWarnings, static::class]);

        if ($shouldShowWarnings) {
            $warnings = static::generateWarnings();
            if (!empty($warnings)) {
                $description .= '<br/>' . $warnings;
            }
        }

        return $description;
    }

    /**
     * @return array<array<string>> of [['id' => (string) 'ID', 'title' => (string) 'TITLE', 'note' => (string) 'NOTE']]
     */
    abstract public static function getUnknownSettings(): array;

    /**
     * @return array<string, string>
     */
    public static function getDetails(): array
    {
        return [
            'id' => static::getName(),
            'title' => static::getTitle(),
            'description' => static::getDescription(),
        ];
    }

    protected static function getPluginManagerInstance(): Manager
    {
        return Manager::getInstance();
    }

    protected static function getSystemDefaultValue()
    {
        return false;
    }

    protected static function getSystemName(): string
    {
        return preg_replace('/\s+/', '', static::getName()) . '_policy_enabled';
    }

    protected static function getSystemType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    protected static function getMeasurableName(): string
    {
        return preg_replace('/\s+/', '', static::getName()) . '_policy_enabled';
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getConfigSection(): string
    {
        return Piwik::getPluginNameOfMatomoClass(static::class);
    }

    protected static function getConfigSettingName(): string
    {
        return static::getSystemName();
    }
    /**
     * If the policy is active at the instance level,
     * disabling the policy for a site will also disable it
     * for the instance.
     */
    public static function setActiveStatus(?int $idSite, bool $isActive): void
    {
        if (isset($idSite)) {
            static::setMeasurableValue($idSite, $isActive);
            if (static::getSystemValue() && !$isActive) {
                static::setSystemValue($isActive);
            }
        } else {
            static::setSystemValue($isActive);
        }

        /**
         * This event is triggered when the status of a compliance policy changes, and
         * is to be used to perform extra actions when a policy is activated/deactivated.
         *
         * The status of a policy cannot be changed via this event.
         *
         * @param bool $isActive Whether the policy is being activated or deactivated
         * @param int|null $idSite
         * @param class-string<CompliancePolicy> The compliance policy in question
         */
        Piwik::postEvent('CompliancePolicy.setActiveStatus', [$isActive, $idSite, static::class]);
    }

    /**
     * If the policy is active at the instance level, then
     * this function will return true for all sites.
     */
    public static function isActive(?int $idSite): bool
    {
        $instanceLevel = static::getSystemValue();
        if (!$instanceLevel && isset($idSite)) {
            return static::getMeasurableValue($idSite);
        }
        return $instanceLevel;
    }

    public static function isConfigControlled()
    {
        return !is_null(static::getConfigValue());
    }
}
