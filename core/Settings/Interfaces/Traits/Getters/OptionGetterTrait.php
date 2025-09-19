<?php

namespace Piwik\Settings\Interfaces\Traits\Getters;

use Piwik\Option;
use Piwik\Settings\Interfaces\OptionSettingInterface;

/**
 * @phpstan-require-implements OptionSettingInterface
 */
trait OptionGetterTrait
{
    /**
     * @return string|null
     */
    public static function getOptionValue()
    {
        $optionValue = Option::get(self::getOptionName());
        if ($optionValue !== false) {
            return $optionValue;
        }
        return null;
    }

    abstract protected static function getOptionName(): string;
}
