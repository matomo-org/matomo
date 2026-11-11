<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\Settings;

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

        $config = new PrivacyManagerConfig($idSite);

        return $config->anonymizeReferrer !== (string) $config->getConfiguredValue('anonymizeReferrer');
    }
}
