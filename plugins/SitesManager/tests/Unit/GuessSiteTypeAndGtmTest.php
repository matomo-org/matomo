<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit;

use Piwik\Plugins\SitesManager\GtmSiteTypeGuesser;
use Piwik\Plugins\SitesManager\SitesManager;

/**
 * @group SitesManager
 * @group GtmSiteTypeGuesserTest
 * @group Plugins
 */
class GuessSiteTypeAndGtmTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GtmSiteTypeGuesser
     */
    private $guesser;

    public function setUp(): void
    {
        parent::setUp();

        $this->guesser = new GtmSiteTypeGuesser();
    }

    public function testSiteTypeUnknownIfResponseFalse()
    {
        $this->assertEquals(SitesManager::SITE_TYPE_UNKNOWN, $this->guesser->guessSiteTypeFromResponse(false));
    }

    public function testGtmIsFalseIfResponseFalse()
    {
        $this->assertFalse($this->guesser->guessGtmFromResponse(false));
    }

    public function testGtmIsTrue()
    {
        $response = [
            'status' => 200,
            'headers' => [],
            'data' => 'it contains gtm.start somewhere'
        ];

        $this->assertTrue($this->guesser->guessGtmFromResponse($response));
    }

    /**
     * @dataProvider responseProvider
     */
    public function testSiteTypesByResponse($expected, $response)
    {
        $this->assertEquals($expected, $this->guesser->guessSiteTypeFromResponse($response));
    }

    public function responseProvider()
    {
        return [
            [SitesManager::SITE_TYPE_UNKNOWN, [
                'status' => 200,
                'headers' => [],
                'data' => 'nothing special'
            ]],
            [SitesManager::SITE_TYPE_SHOPIFY, [
                'status' => 200,
                'headers' => [],
                'data' => 'contains Shopify.theme text'
            ]],
            [SitesManager::SITE_TYPE_WORDPRESS, [
                'status' => 200,
                'headers' => [],
                'data' => 'contains /wp-content text'
            ]],
            [SitesManager::SITE_TYPE_WIX, [
                'status' => 200,
                'headers' => [],
                'data' => 'contains X-Wix-Published-Version text'
            ]],
            [SitesManager::SITE_TYPE_SQUARESPACE, [
                'status' => 200,
                'headers' => [],
                'data' => 'contains <!-- This is Squarespace. --> text'
            ]],
            [SitesManager::SITE_TYPE_SHAREPOINT, [
                'status' => 200,
                'headers' => [],
                'data' => 'contains content="Microsoft SharePoint text'
            ]],
            [SitesManager::SITE_TYPE_JOOMLA, [
                'status' => 200,
                'headers' => ['expires' => 'Wed, 17 Aug 2005 00:00:00 GMT'],
                'data' => 'nothing special'
            ]],
        ];
    }
}
