<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;

/**
 * Per user record of which dashboard promotions have been shown and dismissed.
 *
 * Triggers are evaluated per website, but this state deliberately is not: dismissing a
 * promotion on one website silences it on every website the user has access to. It is
 * stored in the user scoped settings table, so it is removed together with the user and
 * needs no migration.
 */
class UserPromotionState
{
    public const GLOBAL_COOLDOWN_IN_DAYS = 7;

    public const PRODUCT_COOLDOWN_IN_MONTHS = 6;

    private const PLUGIN_NAME = 'ProfessionalServices';

    private const STORE_KEY = 'dashboardPromotions';

    private UserScopedSettingsAccessManager $accessManager;

    public function __construct(UserScopedSettingsAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * True while no triggered promotion may be shown to this user at all, on any website.
     */
    public function isInGlobalCooldown(): bool
    {
        $state = $this->load();

        return $this->isInFuture($state['globalCooldownUntil'] ?? 0);
    }

    /**
     * True while this plugin may not be promoted to this user, on any website. Shared by
     * every trigger promoting the same plugin.
     */
    public function isProductInCooldown(string $pluginName): bool
    {
        $state = $this->load();

        return $this->isInFuture($state['products'][$pluginName]['productCooldownUntil'] ?? 0);
    }

    /**
     * Records an explicit dismissal, starting both the global and the product cooldown.
     */
    public function dismiss(string $pluginName, string $triggerName): void
    {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return;
        }

        $now = Date::factory(Date::getNowTimestamp());
        $state = $this->load();

        $state['globalCooldownUntil'] = $now->addDay(self::GLOBAL_COOLDOWN_IN_DAYS)->getTimestamp();

        $product = $state['products'][$pluginName] ?? [];
        $product['lastDismissedAt'] = $now->getTimestamp();
        $product['lastTriggerName'] = $triggerName;
        $product['productCooldownUntil'] = $now->addPeriod(self::PRODUCT_COOLDOWN_IN_MONTHS, 'month')->getTimestamp();
        $state['products'][$pluginName] = $product;

        $this->save($userLogin, $state);
    }

    /**
     * Notes that the promotion was displayed. Displaying starts no cooldown, so this is
     * informational only and is written at most once per day to keep dashboard requests
     * free of repeated writes.
     */
    public function recordShown(string $pluginName, string $triggerName): void
    {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return;
        }

        $now = Date::factory(Date::getNowTimestamp());
        $state = $this->load();
        $lastShownAt = $state['products'][$pluginName]['lastShownAt'] ?? null;

        if (!empty($lastShownAt) && Date::factory((int) $lastShownAt)->toString() === $now->toString()) {
            return;
        }

        $product = $state['products'][$pluginName] ?? [];
        $product['lastShownAt'] = $now->getTimestamp();
        $product['lastTriggerName'] = $triggerName;
        $state['products'][$pluginName] = $product;

        $this->save($userLogin, $state);
    }

    /**
     * @return array<string, mixed>
     */
    private function load(): array
    {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return [];
        }

        $value = $this->accessManager->get(self::PLUGIN_NAME, $userLogin, self::STORE_KEY, []);

        return is_array($value) ? $value : [];
    }

    /**
     * @param array<string, mixed> $state
     */
    private function save(string $userLogin, array $state): void
    {
        $this->accessManager->set(self::PLUGIN_NAME, $userLogin, self::STORE_KEY, $state);
    }

    /**
     * @param mixed $timestamp
     */
    private function isInFuture($timestamp): bool
    {
        return !empty($timestamp) && (int) $timestamp > Date::getNowTimestamp();
    }
}
