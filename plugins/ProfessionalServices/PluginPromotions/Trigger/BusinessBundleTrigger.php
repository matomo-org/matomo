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
 * Triggers when an instance of 5 to 20 users already uses several premium products and
 * holds no bundle that covers the Business bundle. See {@see PremiumBundleTrigger}.
 */
class BusinessBundleTrigger extends PremiumBundleTrigger
{
    public const NAME = 'multi_product_business';

    public const MINIMUM_USERS = 5;

    public const MAXIMUM_USERS = 20;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getBundleName(): string
    {
        return PremiumBundle::BUSINESS;
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
