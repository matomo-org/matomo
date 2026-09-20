<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;

use Matomo\Network\IPUtils;
use Piwik\Http;

class DigitalOcean implements IpRangeProviderInterface
{
    public function getRanges(): array
    {
        $digitalOcean = Http::sendHttpRequest('https://www.digitalocean.com/geo/google.csv', 120, null, null, 0, false, false, true);

        if (empty($digitalOcean['status']) || $digitalOcean['status'] != 200) {
            throw new \Exception('Failed to retrieve digital ocean IP ranges');
        }

        if (empty($digitalOcean['data'])) {
            return [];
        }

        $ranges = $this->parseRanges($digitalOcean['data']);

        if (empty($ranges)) {
            throw new \Exception('Failed to retrieve any digital ocean IP range.');
        }

        return $ranges;
    }

    /**
     * The CSV holds one entry per line with the IP range in the first column,
     * eg `5.101.96.0/21,NL,NL-NH,Amsterdam,1098 XH`.
     *
     * @return string[]
     */
    public function parseRanges(string $csv): array
    {
        // a UTF-8 BOM would not be removed by trim() and would corrupt the first range
        if (strpos($csv, "\xEF\xBB\xBF") === 0) {
            $csv = substr($csv, 3);
        }

        $ranges = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($csv)) as $line) {
            $row = str_getcsv($line);

            // str_getcsv('') returns [null], so skip blank lines instead of passing null on
            if (!isset($row[0]) || trim($row[0]) === '') {
                continue;
            }

            $range = IPUtils::sanitizeIpRange($row[0]);

            if (null !== $range) {
                $ranges[] = $range;
            }
        }

        return $ranges;
    }
}
