<?php declare(strict_types=1);

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\SitesManager\SiteContentDetection\RaygunCrashReporting;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class RaygunCrashReportingTest extends TestCase
{
    /**
     * @dataProvider responseProvider
     *
     * @param array<string, string> $headers
     */
    public function testDetectByContent(bool $expected, string $data, array $headers)
    {
        $detection = new RaygunCrashReporting();
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

        yield 'no raygun.min.js content' => [
            false,
            'nothing special',
            []
        ];

        yield 'raygun.min.js, crash reporting unconfigured' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/raygun.min.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'raygun.min.js, crash reporting disabled' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>rg4js('enableCrashReporting', false);</script><script src='https://localhost.com/js/raygun.min.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'raygun.min.js, crash reporting enabled, double quote' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>rg4js(\"enableCrashReporting\", true);</script><script src='https://localhost.com/js/raygun.min.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'raygun.min.js, crash reporting enabled, single quote' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>rg4js('enableCrashReporting', true);</script><script src='https://localhost.com/js/raygun.min.js'></script></head><body>A site</body></html>",
            []
        ];
    }
}
