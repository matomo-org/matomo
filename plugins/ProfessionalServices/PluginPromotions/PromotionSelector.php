<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

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

        // One promotion is active at a time for a user, across every website. Once they
        // have been shown one it holds the slot, so a website whose own promotion would
        // otherwise fire shows nothing until the slot is free again.
        $active = $this->userState->getActivePromotion();
        $held = $this->resolveHeldPromotion($active);

        // Whatever released the slot, the recorded outcome goes with it. Keeping $active
        // while falling back to the full ladder would hand the released promotion's figures
        // to whichever promotion is shown instead.
        if (null === $held) {
            $active = null;
        }

        $candidates = null === $held ? $this->registry->getAllByPriority() : [$held];

        foreach ($candidates as $promotion) {
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

            // Whether to show it is answered afresh for this website; what it says is not.
            // The outcome recorded when the promotion took the slot is shown again, so the
            // number in the copy does not move as later weeks are archived.
            if (null !== $active && !empty($active['result'])) {
                $result = Trigger\TriggerResult::fromArray($active['result']);
            }

            return new SelectedPromotion($promotion, $result);
        }

        return null;
    }

    /**
     * The promotion holding the slot, or null once it has been given up.
     *
     * Every reason the slot can be released is decided here, in one place, so that the
     * caller has a single answer to act on. Releasing it in more than one place is what
     * allowed a released promotion's recorded outcome to still be applied to a different
     * promotion.
     *
     * @param array{pluginName: string, triggerName: string}|null $active
     */
    private function resolveHeldPromotion(?array $active): ?Promotion
    {
        if (null === $active) {
            return null;
        }

        // It can no longer be shown at all - its plugin was installed, it was dismissed
        // into cooldown, or a trial is pending on it. Handing the slot back here rather
        // than after the loop lets the ladder be reconsidered on this very request, instead
        // of leaving the user with a blank dashboard once.
        if (!$this->isStillShowable($active)) {
            $this->userState->releaseActivePromotion();

            return null;
        }

        $promotion = $this->registry->findByPluginAndTrigger($active['pluginName'], $active['triggerName']);

        // It no longer exists - renamed or removed between releases - and must not wedge
        // the slot shut for good.
        if (null === $promotion) {
            $this->userState->releaseActivePromotion();

            return null;
        }

        return $promotion;
    }

    /**
     * Whether the promotion holding the slot could still be shown to this user somewhere,
     * ignoring whether its trigger fires for the website in front of them.
     *
     * @param array{pluginName: string, triggerName: string} $active
     */
    private function isStillShowable(array $active): bool
    {
        $pluginName = $active['pluginName'];

        return $this->eligibility->isAllowedForPlugin($pluginName)
            && !$this->userState->isProductInCooldown($pluginName)
            && !$this->isTrialPending($pluginName);
    }

    private function evaluate(Promotion $promotion, int $idSite): ?Trigger\TriggerResult
    {
        try {
            return $promotion->getTrigger()->evaluate($idSite);
        } catch (\Throwable $e) {
            // A promotion is never important enough to break a dashboard. Throwable, not
            // Exception: a TypeError or a ValueError out of a trigger would otherwise
            // escape and take the dashboard with it.
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
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->debug(
                'Could not check whether a trial is pending for {plugin}: {message}',
                ['plugin' => $pluginName, 'message' => $e->getMessage()]
            );

            return false;
        }
    }
}
