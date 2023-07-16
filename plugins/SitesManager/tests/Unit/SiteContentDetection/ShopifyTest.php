<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Shopify;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class ShopifyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testRunSiteDetectionByContent($expected, $data, $headers)
    {
        $detection = new Shopify();
        self::assertSame($expected, $detection->runSiteDetectionByContent($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no shopify content' => [
            false,
            'nothing special',
            []
        ];

        yield 'Shopify.theme is found' => [
            true,
            'contains Shopify.theme text',
            []
        ];

        yield 'Shopify.theme in incorrect case' => [
            false,
            'contains shopify.Theme text',
            []
        ];
    }
}
