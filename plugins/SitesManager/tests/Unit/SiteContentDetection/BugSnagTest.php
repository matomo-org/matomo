<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\SitesManager\SiteContentDetection\BugSnag;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class BugSnagTest extends TestCase
{
    /**
     * @dataProvider responseProvider
     *
     * @param array<string, string> $headers
     */
    public function testDetectByContent(bool $expected, string $data, array $headers)
    {
        $detection = new BugSnag();
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

        yield 'no bugsnag.min.js content' => [
            false,
            'nothing special',
            []
        ];

        yield 'bugsnag.min.js used' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/bugsnag.min.js'></script></head><body>A site</body></html>",
            []
        ];
    }
}
