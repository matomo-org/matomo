<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Option;

trait OptionGetterTrait
{
    public static function getOptionValue()
    {
        return Option::get(self::getOptionName());
    }

    abstract protected static function getOptionName(): string;
}
