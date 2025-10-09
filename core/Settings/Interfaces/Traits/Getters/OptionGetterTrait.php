<?php

namespace Piwik\Settings\Interfaces\Traits\Getters;

use Piwik\Option;
use Piwik\Settings\Interfaces\OptionSettingInterface;

/**
 * @phpstan-require-implements OptionSettingInterface
 */
trait OptionGetterTrait
{
    public static function getOptionValue(?int $idSite = null): ?string
    {
        $optionValue = Option::get(self::getOptionName($idSite));
        if ($optionValue !== false) {
            return $optionValue;
        }
        return null;
    }

    abstract protected static function getOptionName(?int $idSite = null): string;
}
