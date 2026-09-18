<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\CustomBranding;

/**
 * Triggers on an instance that has already replaced Matomo's logo with its own, which is
 * as far as core branding goes and the point at which rebranding the rest is worth
 * mentioning.
 *
 * Evaluated against the instance's current state rather than a report, so it is not
 * cached: a logo uploaded at 10:00 counts straight away.
 */
class CustomLogoTrigger implements PromotionTrigger
{
    public const NAME = 'custom_logo';

    private CustomBranding $branding;

    public function __construct(CustomBranding $branding)
    {
        $this->branding = $branding;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        if (!$this->branding->hasCustomLogo()) {
            return TriggerResult::notTriggered();
        }

        // The copy for this one names no figure, so there is nothing to report beyond the
        // fact that it applies.
        return TriggerResult::triggered([]);
    }
}
