<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration\BlockedIpRanges;

use Matomo\Network\IPUtils;
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
            if (strpos($range, ':') === false) {
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
