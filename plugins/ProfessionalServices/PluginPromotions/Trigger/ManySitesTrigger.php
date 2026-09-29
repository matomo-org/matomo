<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

/**
 * Triggers for a user who watches several measurables that are all actually being used,
 * which is the shape of an estate big enough for front end errors to go unnoticed.
 *
 * Shares its condition with {@see MultipleActiveSitesTrigger}, which pitches a portfolio
 * view off the same facts. See {@see ActiveSitesTrigger} for how the count is made.
 */
class ManySitesTrigger extends ActiveSitesTrigger
{
    public const NAME = 'many_sites';

    public function getName(): string
    {
        return self::NAME;
    }
}
