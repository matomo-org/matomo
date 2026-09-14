<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TriggerResult;

/**
 * The one promotion that will be shown on this dashboard request, together with the
 * trigger outcome that made it relevant.
 */
class SelectedPromotion
{
    private Promotion $promotion;

    private TriggerResult $triggerResult;

    public function __construct(Promotion $promotion, TriggerResult $triggerResult)
    {
        $this->promotion = $promotion;
        $this->triggerResult = $triggerResult;
    }

    public function getPromotion(): Promotion
    {
        return $this->promotion;
    }

    public function getTriggerResult(): TriggerResult
    {
        return $this->triggerResult;
    }
}
