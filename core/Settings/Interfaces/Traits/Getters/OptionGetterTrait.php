<?php

namespace Piwik\Settings\Interfaces\Traits\Getters;

use Piwik\Option;

/**
 * @phpstan-require-implements OptionSettingInterface
 */
trait OptionGetterTrait
{
    /**
     * @return string|false
     */
    public static function getOptionValue()
    {
        return Option::get(self::getOptionName());
    }

    abstract protected static function getOptionName(): string;
}
