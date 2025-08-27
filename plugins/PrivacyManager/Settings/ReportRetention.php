<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Policy\Settings\SettingValueInterface;
use Piwik\Policy\Settings\Traits\PolicyComparisonTrait;
use Piwik\Policy\Settings\Traits\Getters\ConfigGetterTrait;
use Piwik\Policy\Policies\CnilPolicy;
use Piwik\Policy\Policies\HipaaPolicy;

class ReportRetention implements SettingValueInterface
{
    use ConfigGetterTrait;
    use PolicyComparisonTrait;

    /** @var int|null */
    private $value;

    private function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected static function getConfigSettingName(): string
    {
        return 'delete_logs_older_than';
    }

    protected static function getConfigSection(): string
    {
        return 'Deletelogs';
    }

    public static function getPolicyValues(?int $idSite): array
    {
        $policyValues = [];
        $policyValues[CnilPolicy::getName()] = CnilPolicy::isActive($idSite) ? 90 : null;
        $policyValues[HipaaPolicy::getName()] = HipaaPolicy::isActive($idSite) ? 120 : null;

        return $policyValues;
    }

    public static function getInstance(?int $idSite = null)
    {
        $values = self::getPolicyValues($idSite);
        $values['config'] = self::getConfigValue();
        $strictest = self::getStrictestValueFromArray($values);
        return new self($strictest);
    }

    protected static function compareStrictness($value1, $value2)
    {
        if (is_null($value1)) {
            if (is_null($value2)) {
                return null;
            }
            return $value2;
        }
        if (is_null($value2)) {
            return $value1;
        }
        if ($value1 <= $value2) {
            return $value1;
        }
        return $value2;
    }
}
