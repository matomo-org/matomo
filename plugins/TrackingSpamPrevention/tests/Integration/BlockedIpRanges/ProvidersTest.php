<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration\BlockedIpRanges;

use Matomo\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group BlockedIpRangesTest
 * @group Plugins
 */
class ProvidersTest extends IntegrationTestCase
{
    /**
     * @dataProvider getIpRangeProviderDataProvider
     */
    public function testGetRanges(BlockedIpRanges\IpRangeProviderInterface $provider, bool $expectsIpv6)
    {
        $this->serveRecordedResponses();

        $ranges = $provider->getRanges();
        $this->assertNotEmpty($ranges);
        $this->assertTrue(is_array($ranges));
        $this->assertGreaterThan(50, count($ranges));

        $invalid = [];
        $hasIpv4 = false;
        $hasIpv6 = false;

        foreach ($ranges as $range) {
            if (!is_string($range) || null === IPUtils::sanitizeIpRange($range)) {
                $invalid[] = $range;
                continue;
            }
            if (!str_contains($range, ':')) {
                $hasIpv4 = true;
            } else {
                $hasIpv6 = true;
            }
        }

        // all entries are checked above, only the first few are reported to keep the failure readable
        $this->assertSame([], array_slice($invalid, 0, 10), 'The provider returned ' . count($invalid) . ' value(s) that are not valid IP ranges');
        $this->assertTrue($hasIpv4, 'The provider did not return any IPv4 range');

        if ($expectsIpv6) {
            $this->assertTrue($hasIpv6, 'The provider did not return any IPv6 range');
        }
    }

    public function testGetDownloadUrlAzure()
    {
        $this->serveRecordedResponses();

        $azure = new BlockedIpRanges\Azure();
        $url = $azure->getDownloadUrl();
        $this->assertStringStartsWith('https://download.microsoft.com/download/', $url);
        $substr = trim($url, '.json');
        $parts = explode('_', $substr);
        $dateStr = $parts[count($parts) - 1];
        $this->assertSame(8, strlen($dateStr), 'The string should be a valid Ymd (8 digit) date');
        $time = strtotime($dateStr);
        $this->assertGreaterThan(0, $time, 'The date string should have parsed into a valid time');
    }

    /**
     * The providers' servers fail CI runner requests now and then, so the tests use recorded responses: an excerpt
     * of the Azure download page and trimmed copies of each provider's IP range list.
     */
    private function serveRecordedResponses(): void
    {
        Piwik::addAction('Http.sendHttpRequest', function ($url, $params, &$response, &$status, &$headers) {
            $resources = __DIR__ . '/../../resources/';

            if (str_starts_with($url, 'https://www.microsoft.com/en-us/download/details.aspx?id=56519')) {
                $response = file_get_contents($resources . 'azure-download-page.html');
            } elseif (str_starts_with($url, 'https://download.microsoft.com/download/')) {
                $response = file_get_contents($resources . 'azure-service-tags.json');
            } elseif ($url === 'https://www.digitalocean.com/geo/google.csv') {
                $response = file_get_contents($resources . 'digitalocean-google.csv');
                $status = 200;
            } elseif ($url === 'https://ip-ranges.amazonaws.com/ip-ranges.json') {
                $response = file_get_contents($resources . 'aws-ip-ranges.json');
            } elseif ($url === 'https://www.gstatic.com/ipranges/cloud.json') {
                $response = file_get_contents($resources . 'gcloud-ip-ranges.json');
            } elseif ($url === 'https://docs.oracle.com/en-us/iaas/tools/public_ip_ranges.json') {
                $response = file_get_contents($resources . 'oracle-ip-ranges.json');
            }
        });
    }

    public function getIpRangeProviderDataProvider()
    {
        // oracle only publishes IPv4 ranges, all other providers publish both
        return [
            [new BlockedIpRanges\Aws(), true],
            [new BlockedIpRanges\Azure(), true],
            [new BlockedIpRanges\DigitalOcean(), true],
            [new BlockedIpRanges\Gcloud(), true],
            [new BlockedIpRanges\Oracle(), false],
        ];
    }
}
