<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;

use Piwik\Http;

class Gcloud implements IpRangeProviderInterface
{
    public function getRanges(): array
    {
        $ranges = [];

        $gcloud = Http::sendHttpRequest('https://www.gstatic.com/ipranges/cloud.json', 120);

        if (empty($gcloud)) {
            throw new \Exception('Failed to retrieve gcloud IP ranges');
        }

        $gcloud = json_decode($gcloud, true);

        if (empty($gcloud)) {
            throw new \Exception('Failed to retrieve gcloud IP ranges: ' . json_last_error_msg());
        }

        if (empty($gcloud['prefixes'])) {
            throw new \Exception('Failed to retrieve gcloud IP range values');
        }

        foreach ($gcloud['prefixes'] as $prefix) {
            if (isset($prefix['ipv4Prefix'])) {
                $ranges[] = $prefix['ipv4Prefix'];
            }
            if (isset($prefix['ipv6Prefix'])) {
                $ranges[] = $prefix['ipv6Prefix'];
            }
        }

        if (empty($ranges)) {
            throw new \Exception('Failed to retrieve any gcloud IP range.');
        }

        return $ranges;
    }
}
