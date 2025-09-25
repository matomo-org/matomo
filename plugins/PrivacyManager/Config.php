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
 * @property int  $anonymizeUserId      If enabled, it will pseudo anonymize the User ID
 * @property int  $anonymizeOrderId     If enabled, it will anonymize the Order ID
 * @property string  $anonymizeReferrer  Whether the referrer should be anonymized and how it much it should be anonymized
 * @property bool $randomizeConfigId    If enabled, Matomo will generate a new random Config ID (fingerprint) for each tracking request
 */
class Config
{
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

    public static function prefix(string $optionName): string
    {
        return 'PrivacyManager.' . $optionName;
    }

    private function getFromTrackerCache($name, $config)
    {
        $name  = self::prefix($name);
        $cache = Cache::getCacheGeneral();

        if (array_key_exists($name, $cache)) {
            $value = $cache[$name];
            settype($value, $config['type']);

            return $value;
        }

        return $config['default'];
    }

    private function getFromOption($name, $config)
    {
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
        if ($featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            if ($name === 'ipAddressMaskLength') {
                $value = IpAddressMaskLengthSetting::getInstance()->getValue();
            } elseif ($name === 'ipAnonymizerEnabled') {
                $value = IPAnonymisationSetting::getInstance()->getValue();
            } else {
                $name  = self::prefix($name);
                $value = Option::get($name);
            }
        } else {
            $name  = self::prefix($name);
            $value = Option::get($name);
        }

        if (isset($value) && false !== $value) {
            settype($value, $config['type']);
        } else {
            $value = $config['default'];
        }

        return $value;
    }

    private function set($name, $value, $config)
    {
        if ('boolean' == $config['type']) {
            $value = $value ? '1' : '0';
        } else {
            settype($value, $config['type']);
        }

        Option::set(self::prefix($name), $value);
        Cache::clearCacheGeneral();
    }

    public function setTrackerCacheGeneral($cacheContent)
    {
        foreach ($this->properties as $name => $config) {
            $cacheContent[self::prefix($name)] = $this->getFromOption($name, $config);
        }

        return $cacheContent;
    }
}
