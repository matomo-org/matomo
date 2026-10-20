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
    public const GLOBAL_COOLDOWN_IN_DAYS = 18;

    public const PRODUCT_COOLDOWN_IN_MONTHS = 6;

    private const PLUGIN_NAME = 'ProfessionalServices';

    private const STORE_KEY = 'dashboardPromotions';

    /** @var UserScopedSettingsAccessManager */
    private $accessManager;

    /** @var array<string, mixed> */
    /** @var array */
    private $loaded = [];

    /** @var string|null */
    private $loadedFor = null;

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

        // The dismissed promotion must not keep holding the slot, or the global cooldown
        // would expire only for the slot to stay locked to something already refused.
        unset($state['activePromotion']);

        $this->save($userLogin, $state);
    }

    /**
     * Notes that the promotion was displayed. Displaying starts no cooldown, so this is
     * informational only and is written at most once per day to keep dashboard requests
     * free of repeated writes.
     *
     * @param int $idSite the website the outcome was read from
     * @param array<string, mixed>|null $lockedResult the trigger outcome to keep showing for
     *                                                as long as this promotion holds the slot
     */
    public function recordShown(
        string $pluginName,
        string $triggerName,
        int $idSite,
        ?array $lockedResult = null
    ): void {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return;
        }

        $now = Date::factory(Date::getNowTimestamp());
        $state = $this->load();
        $lastShownAt = $state['products'][$pluginName]['lastShownAt'] ?? null;
        $shownToday = !empty($lastShownAt)
            && Date::factory((int) $lastShownAt)->toString() === $now->toString();

        $active = $state['activePromotion'] ?? [];
        $alreadyHeld = ($active['pluginName'] ?? null) === $pluginName
            && ($active['triggerName'] ?? null) === $triggerName;

        // Nothing has changed: this promotion already holds the slot and today's display
        // is already on record. This is the steady state, and it must not write.
        if ($shownToday && $alreadyHeld) {
            return;
        }

        // The daily record is only rewritten once a day, but the claim is not throttled
        // with it. A slot released mid-day - a renamed trigger, a promotion dropped from
        // the registry - can be taken by the same plugin under a second trigger on the
        // same day, and skipping the write there left nothing holding the slot, so the
        // ladder was walked again on every dashboard until midnight.
        if (!$shownToday) {
            $product = $state['products'][$pluginName] ?? [];
            $product['lastShownAt'] = $now->getTimestamp();
            $product['lastTriggerName'] = $triggerName;
            $state['products'][$pluginName] = $product;
        }

        // The figure the copy quotes is settled when the promotion is first shown and kept,
        // so a number the user has already read does not change underneath them as each new
        // week is archived. Only a promotion taking the slot writes it.
        $keepsLock = $alreadyHeld && isset($active['result']);

        $state['activePromotion'] = [
            'pluginName' => $pluginName,
            'triggerName' => $triggerName,
            // Kept with the outcome, because the outcome only describes this website. The
            // same promotion seen on another website quotes that website's own figure
            // instead, and the lock taken here is left alone so that returning to this one
            // still shows the number first read here.
            'idSite' => $keepsLock ? (int) ($active['idSite'] ?? 0) : $idSite,
            'result' => $keepsLock ? $active['result'] : $lockedResult,
        ];

        $this->save($userLogin, $state);
    }

    /**
     * The promotion currently holding this user's single advertising slot, or null when the
     * slot is free.
     *
     * One promotion is shown to a user at a time, across every website they can see. The
     * first one they are shown takes the slot and keeps it: on a website where its own
     * condition is not met they are shown nothing, rather than whatever else would have
     * qualified there. The slot is only ever given up by {@see dismiss()} or by the
     * promotion becoming ineligible - its plugin installed, or a trial pending on it.
     *
     * `result` is the trigger outcome recorded when the promotion took the slot, so the
     * figure in its copy stays the one the user first read rather than being recalculated
     * from each new week's reports, and `idSite` is the website it was read from - the only
     * one it may be shown on.
     *
     * @return array{pluginName: string, triggerName: string, idSite: int, result: array<string, mixed>|null}|null
     */
    public function getActivePromotion(): ?array
    {
        $active = $this->load()['activePromotion'] ?? null;

        if (empty($active['pluginName']) || empty($active['triggerName'])) {
            return null;
        }

        return [
            'pluginName' => (string) $active['pluginName'],
            'triggerName' => (string) $active['triggerName'],
            'idSite' => (int) ($active['idSite'] ?? 0),
            'result' => isset($active['result']) && is_array($active['result']) ? $active['result'] : null,
        ];
    }

    /**
     * Frees the slot, so the next promotion that fires may take it.
     */
    public function releaseActivePromotion(): void
    {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return;
        }

        $state = $this->load();

        if (!isset($state['activePromotion'])) {
            return;
        }

        unset($state['activePromotion']);

        $this->save($userLogin, $state);
    }

    /**
     * The stored state, read once per user per request.
     *
     * Every promotion in the ladder asks whether its product is in cooldown, and the
     * settings table underneath does no caching of its own, so without this the same
     * single row is selected once for each of them.
     *
     * @return array<string, mixed>
     */
    private function load(): array
    {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return [];
        }

        if (($this->loadedFor ?? null) === $userLogin) {
            return $this->loaded;
        }

        $value = $this->accessManager->get(self::PLUGIN_NAME, $userLogin, self::STORE_KEY, []);

        $this->loadedFor = $userLogin;
        $this->loaded = is_array($value) ? $value : [];

        return $this->loaded;
    }

    /**
     * @param array<string, mixed> $state
     */
    private function save(string $userLogin, array $state): void
    {
        $this->accessManager->set(self::PLUGIN_NAME, $userLogin, self::STORE_KEY, $state);

        $this->loadedFor = $userLogin;
        $this->loaded = $state;
    }

    /**
     * @param mixed $timestamp
     */
    private function isInFuture($timestamp): bool
    {
        return !empty($timestamp) && (int) $timestamp > Date::getNowTimestamp();
    }
}
