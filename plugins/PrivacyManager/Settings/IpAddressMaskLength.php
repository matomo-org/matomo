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
use Piwik\Settings\Interfaces\CustomSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\Traits\Getters\CustomGetterTrait;
use Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait;
use Piwik\Policy\CnilPolicy;

/**
 * @implements CustomSettingInterface<int|null>
 * @implements PolicyComparisonInterface<int|null>
 * @implements SettingValueInterface<int|null>
 */
class IpAddressMaskLength implements CustomSettingInterface, PolicyComparisonInterface, SettingValueInterface
{
    /**
     * @use PolicyComparisonTrait<int|null>
     */
    use PolicyComparisonTrait;

    /**
     * @use CustomGetterTrait<int|null>
     */
    use CustomGetterTrait;

    /**
     * @var int|null
     */
    private $value;

    private function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected static function getCustomSettingName(): string
    {
        return 'ipAddressMaskLength';
    }

    public static function getCustomValue(?int $idSite = null)
    {
        // disallowing compliance override to prevent indefinite loop in getting the value
        return (new Config($idSite))->getFromOption(self::getCustomSettingName(), $allowPolicyComplianceOverride = false);
    }

    public static function getTitle(): string
    {
        return Piwik::translate('PrivacyManager_AnonymizeIpMaskLengthSettingTitle');
    }

    public static function getComplianceRequirementNote(?int $idSite = null): string
    {
        // TODO add in logic for generating message for different policy requirements
        $currentValue = self::getInstance($idSite)->getValue();
        return Piwik::translate('PrivacyManager_AnonymizeIpMaskLengthSettingRequirementNote', [ 2, $currentValue ]);
    }

    public static function getInlineHelp(): string
    {
        // custom vue component provides the text
        return '';
    }

    public static function getPolicyRequirements(): array
    {
        $policies = [];
        $policies[CnilPolicy::class] = 2;

        return $policies;
    }

    public static function getInstance(?int $idSite = null): self
    {
        $values = self::getPolicyRequiredValues($idSite);
        $customValue = self::getCustomValue($idSite);
        $values['custom'] = isset($customValue) ? (int) $customValue : null;
        return new self(self::getStrictestValueFromArray($values));
    }

    public static function isCompliant(string $policy, ?int $idSite = null): bool
    {
        $policyValues = self::getPolicyRequirements();

        if (!array_key_exists($policy, $policyValues)) {
            return true;
        }

        $currentValue = self::getInstance($idSite)->getValue();

        return $currentValue >= $policyValues[$policy];
    }

    protected static function compareStrictness($value1, $value2)
    {
        if ($value1 > $value2) {
            return $value1;
        }
        return $value2;
    }
}
