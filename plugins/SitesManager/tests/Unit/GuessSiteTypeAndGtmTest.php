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

        $response['data'] = 'foo bar googletagmanager.js ffoo';
        $this->assertTrue($this->guesser->guessGtmFromResponse($response));
    }

    /**
     * @dataProvider responseProvider
     */
    public function testSiteTypesByResponse($expected, $response)
    {
        $this->assertEquals($expected, $this->guesser->guessSiteTypeFromResponse($response));
    }

    /**
     * All your actual test methods should start with the name "test"
     */
    public function testDetectionOfGa3()
    {
        $response = $this->makeSiteResponse("<html><head></head><body>UA-00000-00</body></html>");
        $this->assertTrue($this->guesser->detectGA3FromResponse($response));

        $response = $this->makeSiteResponse("<html><head></head><body><script src='google-analytics.com/analytics.js'/></body></html>");
        $this->assertTrue($this->guesser->detectGA3FromResponse($response));

        $response = $this->makeSiteResponse("<html><head></head><body><script>window.ga=window.ga;</script></body></html>");
        $this->assertTrue($this->guesser->detectGA3FromResponse($response));

        $response = $this->makeSiteResponse("<html><head></head><body><script>google-ANALYTICS</script></body></html>");
        $this->assertTrue($this->guesser->detectGA3FromResponse($response));
    }

    public function testDetectionOfGa3_noResult()
    {
        $this->assertFalse($this->guesser->detectGA3FromResponse([]));
    }

    public function testDetectionOfGa4()
    {
        $response = $this->makeSiteResponse("<html><head></head><body>G-12345ABC</body></html>");
        $this->assertTrue($this->guesser->detectGA4FromResponse($response));

        $response = $this->makeSiteResponse("<html><head></head><body>properties/1234</body></html>");
        $this->assertTrue($this->guesser->detectGA4FromResponse($response));
    }

    public function testDetectionOfGa4_noResult()
    {
        $this->assertFalse($this->guesser->detectGA4FromResponse([]));
    }

    private function makeSiteResponse($data, $headers = [])
    {
        return ['data' => $data, 'headers' => $headers, 'status' => 200];
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
