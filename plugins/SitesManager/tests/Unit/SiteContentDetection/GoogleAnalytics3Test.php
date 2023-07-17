<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics3;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class GoogleAnalytics3Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new GoogleAnalytics3();
        self::assertSame($expected, $detection->detectByContent($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no GA3 content' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script></script></head><body>A site</body></html>",
            []
        ];

        yield 'GA3 js code found' => [
            true,
            "<html lang=\"en\"><head><title>A site</title></head><script><script>
                     (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                     (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                     m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                     })(window,document,'script','//xxxxxx/analytics.js','ga');
                     ga('create', 'UA-xxxxxxxx-x', 'xxxxxx.com');
                     ga('send', 'pageview');
                     </script></head><body>A site</body></html>",
            []
        ];

        yield 'UA number found' => [
            true,
            "<html><head></head><body>UA-00000-00</body></html>",
            []
        ];

        yield 'GA3 JS file usage found' => [
            true,
            "<html><head></head><body><script src='google-analytics.com/analytics.js'/></body></html>",
            []
        ];

        yield 'GA3 JS window object used' => [
            true,
            "<html><head></head><body><script>window.ga=window.ga;</script></body></html>",
            []
        ];

        yield 'google-analytics found in content' => [
            true,
            "<html><head></head><body><script>google-ANALYTICS</script></body></html>",
            []
        ];
    }
}
