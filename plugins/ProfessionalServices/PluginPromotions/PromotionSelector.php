<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\PluginTrial\Service as PluginTrialService;
use Piwik\Plugins\Marketplace\SiteAwareLinks;

/**
 * Picks the single promotion to display on a dashboard request.
 *
 * Triggers are evaluated for the currently selected website only, while the cooldowns that
 * can rule a promotion out are per user and apply across every website. The selection
 * itself is never cached: cooldowns, trial state and permissions can all change between
 * two dashboard requests.
 *
 * The cheap checks run first, so a user in a global cooldown reads one settings row and
 * nothing else, and a user whose highest priority trigger fires never touches the goals or
 * entry pages archive.
 */
class PromotionSelector
{
    private PromotionRegistry $registry;

    private PromotionEligibility $eligibility;

    private UserPromotionState $userState;

    public function __construct(
        PromotionRegistry $registry,
        PromotionEligibility $eligibility,
        UserPromotionState $userState
    ) {
        $this->registry = $registry;
        $this->eligibility = $eligibility;
        $this->userState = $userState;
    }

    public function select(): ?SelectedPromotion
    {
        if (Piwik::isUserIsAnonymous()) {
            return null;
        }

        $idSite = (new SiteAwareLinks())->getCurrentValidIdSiteOrDefault();
        if (false === $idSite) {
            return null;
        }

        if ($this->userState->isInGlobalCooldown()) {
            return null;
        }

        foreach ($this->registry->getAllByPriority() as $promotion) {
            $pluginName = $promotion->getPluginName();

            if (!$this->eligibility->isAllowedForPlugin($pluginName)) {
                continue;
            }

            if ($this->userState->isProductInCooldown($pluginName)) {
                continue;
            }

            if ($this->isTrialPending($pluginName)) {
                continue;
            }

            $result = $this->evaluate($promotion, (int) $idSite);

            if (null === $result || !$result->isTriggered()) {
                continue;
            }

            return new SelectedPromotion($promotion, $result, (int) $idSite);
        }

        return null;
    }

    private function evaluate(Promotion $promotion, int $idSite): ?Trigger\TriggerResult
    {
        try {
            return $promotion->getTrigger()->evaluate($idSite);
        } catch (Exception $e) {
            // A promotion is never important enough to break a dashboard.
            StaticContainer::get(LoggerInterface::class)->debug(
                'Could not evaluate the {trigger} plugin promotion trigger: {message}',
                ['trigger' => $promotion->getTriggerName(), 'message' => $e->getMessage()]
            );

            return null;
        }
    }

    /**
     * While a trial has been requested for a plugin, promoting it again would only offer
     * the user something they are already waiting for.
     */
    private function isTrialPending(string $pluginName): bool
    {
        try {
            return StaticContainer::get(PluginTrialService::class)->wasRequested($pluginName);
        } catch (Exception $e) {
            return false;
        }
    }
}
