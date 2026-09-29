<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\PremiumBundle;

/**
 * Triggers when an instance of 0 to 4 users already uses several premium products and
 * holds no bundle that covers the Team bundle. See {@see PremiumBundleTrigger}.
 */
class TeamBundleTrigger extends PremiumBundleTrigger
{
    public const NAME = 'multi_product_team';

    public const MINIMUM_USERS = 0;

    public const MAXIMUM_USERS = 4;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getBundleName(): string
    {
        return PremiumBundle::TEAM;
    }

    protected function getMinimumUsers(): int
    {
        return self::MINIMUM_USERS;
    }

    protected function getMaximumUsers(): int
    {
        return self::MAXIMUM_USERS;
    }
}
