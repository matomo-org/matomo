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

        $selected = $this->selectFrom($candidates, (int) $idSite, $active);

        // The held promotion no longer fires on the website it was read from, so it has
        // nothing left to say anywhere: its outcome was recorded against that website, and
        // on every other one it is evaluated afresh and had already failed. Nothing else
        // gives the slot back for this - not the plugin being installed, a dismissal or a
        // trial - so without this the dashboard stays empty for good, with no banner drawn
        // and therefore no dismiss control to release it.
        if (null === $selected && null !== $held && $this->isTheWebsiteItWasReadFrom($active, (int) $idSite)) {
            $this->userState->releaseActivePromotion();

            // Reconsidered on this very request rather than leaving the reader with one
            // blank dashboard, the same as resolveHeldPromotion() does when it hands the
            // slot back.
            $selected = $this->selectFrom($this->registry->getAllByPriority(), (int) $idSite, null);
        }

        return $selected;
    }

    /**
     * Whether the website in front of the reader is the one the held promotion's recorded
     * outcome was read from.
     *
     * @param array{pluginName: string, triggerName: string, idSite: int,
     *              result: array<string, mixed>|null}|null $active
     */
    private function isTheWebsiteItWasReadFrom(?array $active, int $idSite): bool
    {
        return null !== $active && (int) $active['idSite'] === $idSite;
    }

    /**
     * The first of the given promotions that can be shown for this website, or null.
     *
     * @param Promotion[] $candidates
     * @param array{pluginName: string, triggerName: string, idSite: int,
     *              result: array<string, mixed>|null}|null $active
     */
    private function selectFrom(array $candidates, int $idSite, ?array $active): ?SelectedPromotion
    {
        foreach ($candidates as $promotion) {
            $pluginName = $promotion->getPluginName();

            if (!$this->eligibility->isAllowedForPlugin($pluginName)) {
                continue;
            }

            if ($this->userState->isProductInCooldown($pluginName)) {
                continue;
            }

            $result = $this->evaluate($promotion, $idSite);

            if (null === $result || !$result->isTriggered()) {
                continue;
            }

            // Asked only of a promotion that would otherwise be shown. The trigger answer
            // is cached for the day while this is a fresh option read every time - and one
            // that can turn into a write when it finds an expired request - so asking it of
            // all 22 promotions made a steady dashboard render pay for 22 of them.
            if ($this->isTrialPending($pluginName)) {
                continue;
            }

            // Whether to show it is answered afresh for this website; what it says is not.
            // The outcome recorded when the promotion took the slot is shown again, so the
            // number in the copy does not move as later weeks are archived.
            //
            // Only on the website it was read from. A figure, a goal name or an entry page
            // belongs to one website and says nothing true about another, and the report
            // behind it would be addressed with this website's id and that one's goal. On
            // every other website this website's own outcome is what is shown.
            if (null !== $active && !empty($active['result']) && (int) $active['idSite'] === $idSite) {
                $result = Trigger\TriggerResult::fromArray($active['result']);
            }

            return new SelectedPromotion($promotion, $result, $idSite);
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
     * @param array{pluginName: string, triggerName: string, idSite: int,
     *              result: array<string, mixed>|null}|null $active
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
     * @param array{pluginName: string, triggerName: string, idSite: int,
     *              result: array<string, mixed>|null} $active
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
