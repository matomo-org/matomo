<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\ReactJs;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class ReactJsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testRunSiteDetectionByContent($expected, $data, $headers)
    {
        $detection = new ReactJs();
        self::assertSame($expected, $detection->runSiteDetectionByContent($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no react.js content' => [
            false,
            'nothing special',
            []
        ];

        yield 'ReactDOM. is found' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>const root = ReactDOM.createRoot(container);</script></head><body>A site</body></html>",
            []
        ];

        yield 'react.min.js used' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.min.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'react.development.min.js used' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.development.min.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'react-dom.development.min.js used' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react-dom.development.min.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'react.development.js used' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.development.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'react-dom.development.js used' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react-dom.development.js'></script></head><body>A site</body></html>",
            []
        ];
    }
}
