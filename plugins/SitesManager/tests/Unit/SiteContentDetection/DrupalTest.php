<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Drupal;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class DrupalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new Drupal();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no drupal content' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script></script></head><body>A site</body></html>",
            []
        ];

        yield 'Drupal found' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><meta name=\"Generator\" content=\"Drupal\" /></head><body>A site</body></html>",
            []
        ];
    }
}
