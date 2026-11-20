<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

/**
 * A single condition that can make a plugin promotion relevant for a website.
 *
 * Each implementation owns where its data comes from, its threshold, and its caching
 * strategy. Triggers are always evaluated for the currently selected website and within
 * the access context of the requesting user; they never receive a user as an argument.
 */
interface PromotionTrigger
{
    /**
     * Stable identifier used for campaign attribution and for the trigger cache key,
     * eg. `segments` or `bounce_rate`.
     */
    public function getName(): string;

    public function evaluate(int $idSite): TriggerResult;
}
