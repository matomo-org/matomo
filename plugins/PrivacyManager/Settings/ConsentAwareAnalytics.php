<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\Settings;

use Piwik\Plugins\PrivacyManager\SystemSettings;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\Traits\Getters\MeasurableGetterTrait;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Whether a site measures consent-aware analytics.
 *
 * While this is off a site behaves as it always has: a compliance policy treats every visitor the
 * same, whether or not they consented. While it is on, a visitor who consents is measured without
 * the restrictions the policy imposes for those who have not.
 *
 * It is deliberately two settings. The system one turns the feature on for the instance, the
 * measurable one chooses the sites it applies to, so it can be rolled out a site at a time.
 */
class ConsentAwareAnalytics
{
    /** @use MeasurableGetterTrait<bool> */
    use MeasurableGetterTrait;

    public const TRACKER_CACHE_KEY = 'PrivacyManager.consentAwareAnalytics';

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
     * Whether the instance has the feature turned on at all.
     */
    public static function isEnabledForInstance(): bool
    {
        return (bool) (new SystemSettings())->consentAwareAnalytics->getValue();
    }

    /**
     * The value as configured, for writing into the tracker cache.
     */
    public static function isEnabledForSite(int $idSite): bool
    {
        return self::isEnabledForInstance() && (bool) self::getMeasurableValue($idSite);
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
}
