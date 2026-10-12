<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PremiumBundle;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PremiumEntitlements;

/**
 * Shared by the three bundle promotions, which differ only in how many users the instance
 * has and which bundle they pitch.
 *
 * All three want an instance already using several premium products, and none of them may
 * be shown to a consumer who holds that bundle or a larger one. The user ranges do not
 * overlap, so at most one of the three can ever apply to an instance; above the largest
 * range none of them does.
 *
 * Evaluated against the instance's current state rather than a report, so it is not
 * cached: a license bought at 10:00 should silence the promotion straight away.
 */
abstract class PremiumBundleTrigger implements PromotionTrigger
{
    public const MINIMUM_PREMIUM_PRODUCTS = 3;

    private PremiumEntitlements $entitlements;

    private Environment $environment;

    public function __construct(PremiumEntitlements $entitlements, Environment $environment)
    {
        $this->entitlements = $entitlements;
        $this->environment = $environment;
    }

    /**
     * The Marketplace product name of the bundle this promotes.
     */
    abstract protected function getBundleName(): string;

    /**
     * Inclusive bounds on the instance's user count.
     */
    abstract protected function getMinimumUsers(): int;

    abstract protected function getMaximumUsers(): int;

    public function evaluate(int $idSite): TriggerResult
    {
        // The same count Matomo already reports to the Marketplace, so a promotion and the
        // license it leads to are sized off the same number.
        $numUsers = $this->environment->getNumUsers();

        if ($numUsers < $this->getMinimumUsers() || $numUsers > $this->getMaximumUsers()) {
            return TriggerResult::notTriggered();
        }

        // Not every account is offered every bundle, so a promotion for one the customer
        // cannot buy is worse than no promotion at all.
        if (!$this->entitlements->isBundleOffered($this->getBundleName())) {
            return TriggerResult::notTriggered();
        }

        $tierHeld = $this->entitlements->getHighestBundleTierHeld();

        // Unknown is not the same as none. Without an answer from the Marketplace there is
        // no way to tell whether this bundle is already bought, so nothing is promoted.
        if (null === $tierHeld) {
            return TriggerResult::notTriggered();
        }

        if ($tierHeld >= PremiumBundle::getTier($this->getBundleName())) {
            return TriggerResult::notTriggered();
        }

        $numProducts = $this->entitlements->countPremiumProducts();

        if (null === $numProducts || $numProducts < self::MINIMUM_PREMIUM_PRODUCTS) {
            return TriggerResult::notTriggered();
        }

        return TriggerResult::triggered(['count' => $numProducts, 'numUsers' => $numUsers]);
    }
}
