<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;

use Piwik\Http;

class Oracle implements IpRangeProviderInterface
{
    public function getRanges(): array
    {
        $ranges = [];

        $oracle = Http::sendHttpRequest('https://docs.cloud.oracle.com/en-us/iaas/tools/public_ip_ranges.json', 120);

        if (empty($oracle)) {
            throw new \Exception('Failed to retrieve oracle IP ranges');
        }

        $oracle = json_decode($oracle, true);

        if (empty($oracle)) {
            throw new \Exception('Failed to retrieve oracle IP ranges: ' . json_last_error_msg());
        }

        if (empty($oracle['regions'])) {
            throw new \Exception('Failed to retrieve oracle IP range regions');
        }

        foreach ($oracle['regions'] as $region) {
            foreach ($region['cidrs'] as $cidr) {
                if (!isset($cidr['cidr'])) {
                    throw new \Exception('Failed to get oracle address prefixes');
                }
                $ranges[] = $cidr['cidr'];
            }
        }

        if (empty($ranges)) {
            throw new \Exception('Failed to retrieve any oracle IP range.');
        }

        return $ranges;
    }
}
