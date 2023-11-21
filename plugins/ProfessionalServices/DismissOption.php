<?php

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Option;

class DismissOption
{
    private const OPTION_NAME_PREFIX = 'ProfessionalServices.dismissedPromotion.';
    public static function dismissPluginPromotionForUser(string $pluginName, string $userLogin): void
    {
        Option::set(self::getOptionName($pluginName, $userLogin), true);
    }
    public static function hasUserDismissedPluginPromotion(string $pluginName, string $userLogin): bool
    {
        return (bool) Option::get(self::getOptionName($pluginName, $userLogin));
    }

    private static function getOptionName(string $pluginName, string $userLogin): string
    {
        return self::OPTION_NAME_PREFIX.$userLogin.'.'.$pluginName;
    }
}
