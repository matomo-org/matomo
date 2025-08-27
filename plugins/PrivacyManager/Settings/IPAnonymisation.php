<?php

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Policy\Settings\SettingValueInterface;
use Piwik\Policy\Settings\Traits\PolicyComparisonTrait;
use Piwik\Policy\Settings\Traits\Getters\OptionGetterTrait;
use Piwik\Policy\Policies\CnilPolicy;
use Piwik\Policy\Policies\HipaaPolicy;

class IPAnonymisation implements SettingValueInterface
{
    use OptionGetterTrait;
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

    protected static function getOptionName(): string
    {
        return 'PrivacyManager.ipAnonymizerEnabled';
    }

    public static function getPolicyValues(?int $idSite): array
    {
        $policies = [];
        $policies[CnilPolicy::getName()] = CnilPolicy::isActive($idSite) ? 1 : null;
        $policies[HipaaPolicy::getName()] = HipaaPolicy::isActive($idSite) ? 1 : null;
        return $policies;
    }

    public static function getInstance(?int $idSite = null)
    {
        $values = self::getPolicyValues($idSite);
        $values['option'] = self::getOptionValue();
        return new self(self::getStrictestValueFromArray($values));
    }

    protected static function compareStrictness($value1, $value2)
    {
        if (is_null($value1)) {
            return $value2;
        }
        if (is_null($value2)) {
            return $value1;
        }
        if ($value1 > $value2) {
            return $value1;
        }
        return $value2;
    }
}
