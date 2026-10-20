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
    /** @var Promotion */
    private $promotion;

    /** @var TriggerResult */
    private $triggerResult;

    /** @var int */
    private $idSite;

    public function __construct(Promotion $promotion, TriggerResult $triggerResult, int $idSite)
    {
        $this->promotion = $promotion;
        $this->triggerResult = $triggerResult;
        $this->idSite = $idSite;
    }

    public function getPromotion(): Promotion
    {
        return $this->promotion;
    }

    public function getTriggerResult(): TriggerResult
    {
        return $this->triggerResult;
    }

    /**
     * The website the outcome was read from, which everything the banner says belongs to:
     * the figure it quotes, the goal or entry page it names, and the report it links to.
     *
     * Carried with the selection rather than read from the request again, so that the
     * report link cannot be built from one website's id and another's goal.
     */
    public function getIdSite(): int
    {
        return $this->idSite;
    }
}
