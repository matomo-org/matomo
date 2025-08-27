<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Option;
use Piwik\Policy\Settings\OptionSettingInterface;

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
