<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;

use Piwik\Http;

class Aws implements IpRangeProviderInterface
{
    public function getRanges(): array
    {
        $ranges = [];

        $aws = Http::sendHttpRequest(
            'https://ip-ranges.amazonaws.com/ip-ranges.json',
            120,
            null,
            null,
            0,
            false,
            false,
            false,
            'GET',
            null,
            null,
            false
        );

        if (empty($aws)) {
            throw new \Exception('Failed to retrieve AWS IP ranges');
        }

        $aws = json_decode($aws, true);

        if (empty($aws)) {
            throw new \Exception('Failed to retrieve AWS IP ranges: ' . json_last_error_msg());
        }
        if (empty($aws['prefixes'])) {
            throw new \Exception('Failed to retrieve AWS IPv4 range prefixes');
        }
        if (empty($aws['ipv6_prefixes'])) {
            throw new \Exception('Failed to retrieve AWS IPv6 range prefixes');
        }

        foreach ($aws['prefixes'] as $range) {
            if (isset($range['ip_prefix'])) {
                $ranges[] = $range['ip_prefix'];
            }
        }
        foreach ($aws['ipv6_prefixes'] as $range) {
            if (isset($range['ipv6_prefix'])) {
                $ranges[] = $range['ipv6_prefix'];
            }
        }

        if (empty($ranges)) {
            throw new \Exception('Failed to retrieve any AWS IP range.');
        }

        return $ranges;
    }
}
