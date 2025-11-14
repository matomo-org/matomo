<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\Settings\Interfaces\CustomSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\Getters\CustomGetterTrait;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Policy\CnilPolicy;

/**
 * @implements CustomSettingInterface<string|null>
 * @implements PolicyComparisonInterface<string|null>
 * @implements SettingValueInterface<string|null>
 */
class ReferrerAnonymisation implements CustomSettingInterface, PolicyComparisonInterface, SettingValueInterface
{
    /**
     * @use PolicyComparisonTrait<string|null>
     */
    use PolicyComparisonTrait;

    /**
     * @use CustomGetterTrait<string|null>
     */
    use CustomGetterTrait;

    /**
     * @var string|null
     */
    private $value;

    private function __construct(?string $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected static function getCustomSettingName(): string
    {
        return 'anonymizeReferrer';
    }

    public static function getCustomValue(?int $idSite = null)
    {
        // disallowing compliance override to prevent indefinite loop in getting the value
        return (new Config($idSite))->getFromOption(self::getCustomSettingName(), $allowPolicyComplianceOverride = false);
    }

    public static function getTitle(): string
    {
        return Piwik::translate('PrivacyManager_ReferrerAnonymizationSettingTitle');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        return Piwik::translate('PrivacyManager_ReferrerAnonymizationSettingRequirementNote');
    }

    public static function getInlineHelp(): string
    {
        // not used as not a true setting, help text part of FE vue component
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        $policies = [];
        $policies[CnilPolicy::class] = ReferrerAnonymizer::EXCLUDE_PATH;

        return $policies;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyRequiredValues($idSite);
        $customValue = self::getCustomValue($idSite);
        $values['custom'] = $customValue ?? null;
        return new self(self::getStrictestValueFromArray($values));
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        $policyValues = self::getPolicyRequirements();

        if (!array_key_exists($policy, $policyValues)) {
            return true;
        }

        $currentValue = self::getInstance($idSite)->getValue();

        return $currentValue === self::compareStrictness($currentValue, $policyValues[$policy]);
    }

    protected static function compareStrictness($value1, $value2)
    {
        // the list of options in ReferrerAnonymizer::getAvailableAnonymizationOptions is ordered by strictness
        // higher position in the array means stricter value
        $options = array_keys(ReferrerAnonymizer::getAvailableAnonymizationOptions());

        $posValue1 = array_search($value1, $options);
        $posValue2 = array_search($value2, $options);

        if ($posValue1 > $posValue2) {
            return $value1;
        }
        return $value2;
    }
}
