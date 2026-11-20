<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

/**
 * Triggers for a user who watches several websites that are all actually being used.
 *
 * What makes a portfolio view worth having is the number of *other* websites the same
 * person has to keep an eye on. Shares its condition with {@see ManySitesTrigger}; see
 * {@see ActiveSitesTrigger} for how the count is made.
 */
class MultipleActiveSitesTrigger extends ActiveSitesTrigger
{
    public const NAME = 'multiple_active_sites';

    public function getName(): string
    {
        return self::NAME;
    }
}
