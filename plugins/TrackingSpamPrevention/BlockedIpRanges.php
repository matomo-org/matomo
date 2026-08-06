<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Matomo\Network\IP;
use Matomo\Network\IPUtils;
use Piwik\Cache as PiwikCache;
use Piwik\Common;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\IpRangeProviderInterface;
use Piwik\SettingsPiwik;
use Piwik\Tracker\Cache;

class BlockedIpRanges
{
    public const OPTION_KEY = 'TrackingSpamBlockedIpRanges';

    /**
     * @var IpRangeProviderInterface[]
     */
    private $providers;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param IpRangeProviderInterface[] $providers
     */
    public function __construct($providers, Configuration $configuration)
    {
        $this->providers = $providers;
        $this->configuration = $configuration;
    }

    /**
     * will return the array indexed by ip start
     * @return array
     */
    public function getBlockedRanges()
    {
        $ranges = Option::get(self::OPTION_KEY);
        if (empty($ranges)) {
            return [];
        }
        $ranges = json_decode($ranges, true);
        if (empty($ranges)) {
            return [];
        }
        return $ranges;
    }

    /**
     * An array of blocked ranges
     * @param array[] $ranges
     */
    public function setBlockedRanges($ranges)
    {
        // we index them by first character for performance reasons see the excluded method
        Option::set(self::OPTION_KEY, json_encode($ranges));
    }

    private function getIndexForIpOrRange($ipOrRange)
    {
        if (empty($ipOrRange)) {
            return;
        }
        // when IP is 10.11.12.13 then we return "10."
        // when IP is f::0 then we return "f:"
        foreach (['.', ':'] as $searchChar) {
                $posSearchChar = strpos($ipOrRange, $searchChar);
            if ($posSearchChar !== false) {
                return Common::mb_substr($ipOrRange, 0, $posSearchChar) . $searchChar;
            }
        }

        // fallback, should not happen
        return Common::mb_substr($ipOrRange, 0, 1);
    }

    public function isExcluded($ip)
    {
        if (empty($ip)) {
            return false;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // for performance reasons we index ranges by first character of ip. assuming this works in most cases.
        // so we compare less ranges as it is slow to compare many ranges
        $indexed = $this->getIndexForIpOrRange($ip);

        if (empty($indexed)) {
            return false;
        }

        $trackerCache = Cache::getCacheGeneral();
        if (empty($trackerCache[self::OPTION_KEY][$indexed])) {
            return false;
        }

        $ip  = IP::fromStringIP($ip);
        $key = 'TrackingSpamPreventionIsIpInRange' . $ip->toString();

        $cache = PiwikCache::getTransientCache();
        if ($cache->contains($key)) {
            $isInRanges = $cache->fetch($key);
        } else {
            $isInRanges = $ip->isInRanges($trackerCache[self::OPTION_KEY][$indexed]);

            $cache->save($key, $isInRanges);
        }

        return $isInRanges;
    }

    public function banIp($ip)
    {
        $ranges = $this->getBlockedRanges();
        $index = $this->getIndexForIpOrRange($ip);
        if (empty($ranges[$index])) {
            $ranges[$index] = [];
        }

        if (strpos($ip, '.') !== false) {
            $ipRange = $ip . '/32';
        } else {
            $ipRange = $ip . '/128';
        }

        if (!in_array($ipRange, $ranges[$index], true)) {
            $ranges[$index][] = $ipRange;
            $this->setBlockedRanges($ranges);

            /**
             * This event is posted when an IP is being banned from tracking. You can use it for example to notify someone
             * that this IP was banned.
             *
             * @param string $ipRange The IP range that will be blocked
             * @param string $ip The IP that caused this range to be blocked
             */
            Piwik::postEvent('TrackingSpamPrevention.banIp', [$ipRange, $ip]);
        }

        return $ranges;
    }

    public function unsetAllIpRanges()
    {
        $this->setBlockedRanges([]);
    }

    public function updateBlockedIpRanges()
    {
        if (!SettingsPiwik::isInternetEnabled()) {
            $this->unsetAllIpRanges();
            return;
        }

        $ranges = [];

        foreach ($this->providers as $provider) {
            try {
                $ranges = array_merge($ranges, $provider->getRanges());
            } catch (\Exception $e) {
                if ($this->configuration->shouldThrowExceptionOnIpRangeSync()) {
                    throw $e;
                }
            }
        }

        $indexedRange = [];
        foreach ($ranges as $range) {
            // guard against a provider changing its response format. is_string() is needed as
            // sanitizeIpRange() raises a TypeError on eg a nested array
            $range = is_string($range) ? IPUtils::sanitizeIpRange($range) : null;

            if (null === $range) {
                continue;
            }

            $indexed = $this->getIndexForIpOrRange($range);
            if (empty($indexedRange[$indexed])) {
                $indexedRange[$indexed] = [];
            }
            $indexedRange[$indexed][] = $range;
        }
        $this->setBlockedRanges($indexedRange);
    }
}
