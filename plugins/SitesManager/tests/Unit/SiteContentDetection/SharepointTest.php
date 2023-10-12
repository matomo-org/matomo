<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Sharepoint;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class SharepointTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new Sharepoint();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no sharepoint content' => [
            false,
            'nothing special',
            []
        ];

        yield 'sharepoint is found' => [
            true,
            'contains content="Microsoft SharePoint text',
            []
        ];

        yield 'sharepoint in incorrect case' => [
            false,
            'contains content="microsoft sharepoint text',
            []
        ];
    }
}
