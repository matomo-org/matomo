<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\UserId\Settings\UserIdDisabled;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\SystemSettingInterface;
use Piwik\Settings\Interfaces\Traits\Setters\MeasurableSetterTrait;
use Piwik\Settings\Interfaces\Traits\Setters\SystemSetterTrait;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Whether a site measures consent-aware analytics.
 *
 * While this is off a site behaves as it always has: a compliance policy treats every visitor the
 * same, whether or not they consented. While it is on, a visitor who consents is measured without
 * the restrictions the policy imposes for those who have not.
 *
 * It is set on the Compliance page, for the whole instance or for a website. A website measures
 * consent-aware analytics when either is on, so it can be rolled out a website at a time.
 *
 * `[PrivacyManager] consent_aware_analytics` in the config sets the instance value and locks it,
 * as for any system setting. It does not lock the websites, which keep their own value.
 *
 * @implements SystemSettingInterface<bool>
 * @implements MeasurableSettingInterface<bool>
 */
class ConsentAwareAnalytics implements SystemSettingInterface, MeasurableSettingInterface
{
    /** @use SystemSetterTrait<bool> */
    use SystemSetterTrait;

    /** @use MeasurableSetterTrait<bool> */
    use MeasurableSetterTrait;

    public const TRACKER_CACHE_KEY = 'PrivacyManager.consentAwareAnalytics';

    protected static function getSystemName(): string
    {
        return 'consent_aware_analytics';
    }

    protected static function getSystemType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getSystemDefaultValue()
    {
        return false;
    }

    protected static function getMeasurableName(): string
    {
        return 'consent_aware_analytics';
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    /**
     * Whether it is on for the whole instance.
     */
    public static function isEnabledForInstance(): bool
    {
        return (bool) self::getSystemValue();
    }

    /**
     * Whether this site measures consent-aware analytics, as configured for the instance or for
     * the site, for writing into the tracker cache.
     */
    public static function isEnabledForSite(int $idSite): bool
    {
        return self::isEnabledForInstance() || (bool) self::getMeasurableValue($idSite);
    }

    /**
     * Whether the given scope measures it: for a website, when it is on for the whole instance or
     * for that website; without a website, when it is on for the whole instance.
     */
    public static function isEnabledForScope(?int $idSite): bool
    {
        return null === $idSite ? self::isEnabledForInstance() : self::isEnabledForSite($idSite);
    }

    /**
     * The text a compliance setting shows for the given scope: the consent-aware variant, keyed
     * `<key>ConsentAware`, where consent-aware analytics is on for that scope, and the plain one
     * otherwise. A scope that does not measure it treats every visitor the same, so the plain
     * text is what is true there.
     *
     * @param array<int, mixed> $params as for Piwik::translate()
     */
    public static function translateForScope(?int $idSite, string $translationKey, array $params = []): string
    {
        if (self::isEnabledForScope($idSite)) {
            return Piwik::translate($translationKey . 'ConsentAware', $params);
        }

        return Piwik::translate($translationKey, $params);
    }

    /**
     * Turns it on or off for the whole instance, or for a site when one is given.
     */
    public static function setEnabled(bool $enabled, ?int $idSite = null): void
    {
        if (null === $idSite) {
            self::setSystemValue($enabled);
        } else {
            self::setMeasurableValue($idSite, $enabled);
        }

        // every site inherits the instance value, so every site's tracker cache is stale
        TrackerCache::deleteTrackerCache();
    }

    /**
     * Whether this site measures consent-aware analytics, read from the tracker cache so that it
     * costs no query while tracking.
     */
    public static function isEnabled(?int $idSite = null): bool
    {
        if (null === $idSite) {
            return false;
        }

        $cache = TrackerCache::getCacheWebsiteAttributes($idSite);

        return true === ($cache[self::TRACKER_CACHE_KEY] ?? false);
    }

    /**
     * Whether consent is measured while tracking this site: it measures consent-aware analytics
     * and does not force cookieless tracking, under which consent changes nothing.
     *
     * This is Request::measuresConsentAwareAnalytics() for when no request is at hand - a report
     * or segment deciding whether consented visits can have stored what it shows - so that what
     * they offer stays in step with what tracking stores. Read from the tracker cache.
     */
    public static function isMeasuredForSite(int $idSite): bool
    {
        if ($idSite <= 0 || !self::isEnabled($idSite)) {
            return false;
        }

        return !(new PrivacyManagerConfig($idSite))->forceCookielessTracking;
    }

    /**
     * Whether a compliance policy is imposing something on this site that consent can lift.
     *
     * Consent relaxes a restriction that is in force. Where none is - no policy is enforced, or
     * the one that is requires nothing of the settings consent relaxes - a consenting visitor is
     * measured exactly as everyone else, and nothing about their visit should differ either.
     *
     * Read from the tracker cache, so it costs no query. It is only ever asked on a request that
     * already reports consent for an opted-in site.
     */
    public static function hasRelaxableRestriction(?int $idSite = null): bool
    {
        if (null === $idSite) {
            return false;
        }

        $siteAttributes = TrackerCache::getCacheWebsiteAttributes($idSite);

        // The enforced value is the whole of this one, unlike the two below, because the
        // measurable form of the setting is not registered anywhere and so cannot be set: what
        // is cached here only ever comes from a policy. Were it ever exposed, the admin's own
        // value would have to be kept apart from the policy's the way the others are, since
        // consent lifts what a policy imposes and not what the admin chose for themselves.
        if (true === ($siteAttributes[CampaignParameterValuesMasked::class] ?? false)) {
            return true;
        }

        if (
            ($siteAttributes[UserIdDisabled::class] ?? false)
            !== ($siteAttributes[UserIdDisabled::CONFIGURED_VALUE_CACHE_KEY] ?? false)
        ) {
            return true;
        }

        // The third-party cookie is deliberately not counted: consent lifts it per request (see
        // Request::shouldUseThirdPartyCookieForConsent()) and nothing stored on the visit depends on it.

        // every privacy setting consent lifts back to the admin's own value
        return (new PrivacyManagerConfig($idSite))->isConfiguredValueOverriddenByPolicy();
    }
}
