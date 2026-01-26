<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\SitesManager\SiteContentDetection\AmazonCloudFront;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class AmazonCloudFrontTest extends TestCase
{
    /**
     * @dataProvider getResponseTestData
     */
    public function testDetectByContent($expected, $data, $headers)
    {
        $detection = new AmazonCloudFront();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    /**
     * @return iterable<string, array{bool, string, array<string, string>}>
     */
    public function getResponseTestData(): iterable
    {
        yield 'no headers' => [
            false,
            '',
            [],
        ];

        // headers that require a specific value to trigger detection
        $headers = ['server', 'via', 'x-cache'];

        foreach ($headers as $header) {
            yield "header ${header} matching value" => [
                true,
                '',
                [$header => 'contains cloudfront token'],
            ];

            yield "header ${header} other value" => [
                false,
                '',
                [$header => 'something unexpected'],
            ];
        }

        // headers that only need to exist to trigger detection
        $headers = ['x-amz-cf-id', 'x-amz-cf-pop'];

        foreach ($headers as $header) {
            yield "header $header exists" => [
                true,
                '',
                [$header => 'value irrelevant'],
            ];
        }
    }
}
