<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\BotTracking\BotTrackingMethod\BotTrackingMethodAbstract;
use Piwik\Plugins\BotTracking\BotTrackingMethod\Cloudflare;
use Piwik\Plugins\BotTracking\BotTrackingMethod\AmazonCloudFront;
use Piwik\Plugins\BotTracking\BotTrackingMethod\HttpTrackingApi;
use Piwik\Plugins\BotTracking\BotTrackingMethod\MissingOneContactUs;
use Piwik\Plugins\BotTracking\BotTrackingMethod\WordPress;

/**
 * @group BotTracking
 * @group BotTrackingMethod
 * @group Plugins
 */
class BotTrackingMethodTest extends TestCase
{
    /**
     * @dataProvider getBotTrackingMethods
     *
     * @param class-string<BotTrackingMethodAbstract> $method
     */
    public function testSiteContentDetectionSourceExists(string $method): void
    {
        // sanity check to prevent a site content detection
        // class going away without being noticed
        $detectionId = $method::getSiteContentDetectionId();
        self::assertTrue($detectionId === null || is_string($detectionId));
    }

    public function getBotTrackingMethods(): array
    {
        return [
            [AmazonCloudFront::class],
            [Cloudflare::class],
            [HttpTrackingApi::class],
            [MissingOneContactUs::class],
            [WordPress::class],
        ];
    }
}
