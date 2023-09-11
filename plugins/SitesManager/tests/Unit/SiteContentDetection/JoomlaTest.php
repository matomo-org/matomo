<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Joomla;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class JoomlaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new Joomla();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no joomla content' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script></script></head><body>A site</body></html>",
            ['expires' => 'Wed, 17 Aug 2019 00:02:00 GMT']
        ];

        yield 'Joomla header found' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title></head><body>A site</body></html>",
            ['expires' => 'Wed, 17 Aug 2005 00:00:00 GMT']
        ];
    }
}
