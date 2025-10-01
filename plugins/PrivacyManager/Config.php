<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager;

use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Tracker\Cache;
use Piwik\Plugins\PrivacyManager\Settings\IpAddressMaskLength as IpAddressMaskLengthSetting;
use Piwik\Plugins\PrivacyManager\Settings\IPAnonymisation as IPAnonymisationSetting;

/**
 * @property bool $doNotTrackEnabled    Enable / Disable Do Not Track {@see DoNotTrackHeaderChecker}
 * @property bool $ipAnonymizerEnabled  Enable / Disable IP Anonymizer {@see IPAnonymizer}
 * @property bool $useAnonymizedIpForVisitEnrichment Set this setting to 0 to let plugins use the full
 *                                      non-anonymized IP address when enriching visitor information.
 *                                      When set to 1, by default, Geo Location via geoip and Provider reverse name lookups
 *                                      will use the anonymized IP address when anonymization is enabled.
 * @property int  $ipAddressMaskLength  Anonymize a visitor's IP address after testing for "Ip exclude"
 *                                      This value is the level of anonymization Piwik will use; if the IP
 *                                      anonymization is deactivated, this value is ignored. For IPv4/IPv6 addresses,
 *                                      valid values are the number of octets in IP address to mask (from 0 to 4).
 *                                      For IPv6 addresses 0..4 means that 0, 64, 80, 104 or all bits are masked.
 * @property bool $forceCookielessTracking If enabled, Matomo will try to force tracking without cookies
 * @property bool $anonymizeUserId      If enabled, it will pseudo anonymize the User ID
 * @property bool $anonymizeOrderId     If enabled, it will anonymize the Order ID
 * @property string  $anonymizeReferrer  Whether the referrer should be anonymized and how it much it should be anonymized
 * @property bool $randomizeConfigId    If enabled, Matomo will generate a new random Config ID (fingerprint) for each tracking request
 */
class Config
{
    /**
     * If provided, tells the config to only apply to a specific site ID.
     *
     * @var int|null
     */
    private $idSite;

    public function __construct(?int $idSite = null)
    {
        $this->setIdSite($idSite);
    }

    private $properties = array(
        'useAnonymizedIpForVisitEnrichment' => array('type' => 'boolean', 'default' => false),
        'ipAddressMaskLength'               => array('type' => 'integer', 'default' => 2),
        'doNotTrackEnabled'                 => array('type' => 'boolean', 'default' => false),
        'ipAnonymizerEnabled'               => array('type' => 'boolean', 'default' => true),
        'forceCookielessTracking'           => array('type' => 'boolean', 'default' => false),
        'anonymizeUserId'                   => array('type' => 'boolean', 'default' => false),
        'anonymizeOrderId'                  => array('type' => 'boolean', 'default' => false),
        'anonymizeReferrer'                 => array('type' => 'string', 'default' => ''),
        'randomizeConfigId'                 => array('type' => 'boolean', 'default' => false),
    );

    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new \Exception(sprintf('Property %s does not exist', $name));
        }

        $this->set($name, $value, $this->properties[$name]);
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new \Exception(sprintf('Property %s does not exist', $name));
        }

        return $this->getFromTrackerCache($name, $this->properties[$name]);
    }

    public function prefix(string $optionName, bool $addIdSite = true): string
    {
        // if requested, adding the site ID in the middle to have all the site-specific settings together
        return 'PrivacyManager.' . (($addIdSite && $this->idSite) ? "idSite($this->idSite)." : '') . $optionName;
    }

    private function getFromSpecificTrackerCache(string $name, array $cache, array $config, bool $useFallback = true)
    {
        if (array_key_exists($name, $cache)) {
            $value = $cache[$name];
            settype($value, $config['type']);

            return $value;
        }

        return $useFallback ? $config['default'] : null;
    }

    private function getFromTrackerCache(string $name, array $config)
    {
        $generalCache = Cache::getCacheGeneral();
        $name = $this->prefix($name, false); // when getting from tracker cache, we always want the generic name
        if ($this->idSite) {
            $cache = Cache::getCacheWebsiteAttributes($this->idSite);
        } else {
            $cache = $generalCache; // so that we always have some cache to check below
        }

        // check specific cache first, if no value found there return from general cache or use default
        $valueSite = $this->getFromSpecificTrackerCache($name, $cache, $config, $useFallback = false);
        $valueGeneralWithFallback = $this->getFromSpecificTrackerCache($name, $generalCache, $config);

        return $valueSite ?? $valueGeneralWithFallback;
    }

    private function getFromOption(string $name, array $config)
    {
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
        if ($featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            if ($name === 'ipAddressMaskLength') {
                $value = IpAddressMaskLengthSetting::getInstance()->getValue();
            } elseif ($name === 'ipAnonymizerEnabled') {
                $value = IPAnonymisationSetting::getInstance()->getValue();
            } else {
                $value = Option::get($this->prefix($name));
            }
        } else {
            $value = Option::get($this->prefix($name));
        }

        // fallback to global settings if we don't have specific site settings saved
        if (false === $value && !$this->hasSiteSpecificSettings($name)) {
            $value = Option::get($this->prefix($name, false));
        }

        if (isset($value) && false !== $value) {
            settype($value, $config['type']);
        } else {
            $value = $config['default'];
        }

        return $value;
    }

    private function set($name, $value, $config): void
    {
        if ('boolean' == $config['type']) {
            $value = $value ? '1' : '0';
        } else {
            settype($value, $config['type']);
        }

        Option::set($this->prefix($name), $value);
        Cache::deleteTrackerCache();
    }

    public function setIdSite(?int $idSite): void
    {
        if (null === $idSite || $idSite > 0) {
            $this->idSite = $idSite;
        }
    }

    public function setTrackerCache(array &$cacheContent): array
    {
        foreach ($this->properties as $name => $config) {
            // when setting tracker cache, we always want generic name
            $cacheContent[$this->prefix($name, false)] = $this->getFromOption($name, $config);
        }

        return $cacheContent;
    }

    public function getConfigPropertyNames(): array
    {
        return array_keys($this->properties);
    }

    public function removeForSite(): void
    {
        if ($this->idSite) {
            Option::deleteLike($this->prefix('%'));
        }
    }

    private function hasSiteSpecificSettings(string $name = '%'): bool
    {
        return $this->idSite && count(Option::getLike($this->prefix($name))) > 0;
    }

    public function useSiteSpecificSettings(): bool
    {
        if (!$this->idSite) {
            return false;
        }

        return $this->hasSiteSpecificSettings();
    }
}
