<?php

declare(strict_types=1);

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\SitesManager\SiteContentDetection\Rollbar;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class RollbarTest extends TestCase
{
    /**
     * @dataProvider responseProvider
     *
     * @param array<string, string> $headers
     */
    public function testDetectByContent(bool $expected, string $data, array $headers)
    {
        $detection = new Rollbar();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    /**
     * @return iterable<string, array{bool, string, array<string, string>}>
     */
    public function responseProvider(): iterable
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no rollbar.min.js content' => [
            false,
            'nothing special',
            []
        ];

        yield 'rollbar.min.js used' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/rollbar.min.js'></script></head><body>A site</body></html>",
            []
        ];
    }
}
