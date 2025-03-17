<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\tests\Integration;

use Piwik\DataTable\Renderer;
use Piwik\Piwik;
use Piwik\Plugins\SEO\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SEO
 * @group SEOTest
 * @group Plugins
 */
class SEOTest extends IntegrationTestCase
{
    private static $userAgents = [
        'Mozilla/5.0 ArchLinux (X11; U; Linux x86_64; en-US) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.100 Safari/534.30',
        'Mozilla/5.0 (Windows Mobile 10; Android 10.0; Microsoft; Lumia 950XL) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Mobile Safari/537.36 Edge/40.15254.603',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.3124.72',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Vivaldi/7.1.3570.60',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_7_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Vivaldi/7.1.3570.60',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14.7; rv:136.0) Gecko/20100101 Firefox/136.0',
        'Mozilla/5.0 (Android 15; Mobile; rv:136.0) Gecko/136.0 Firefox/136.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/134.0.6998.99 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (iPod touch; CPU iPhone 17_7_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Vivaldi/7.1.3570.60',
        'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Vivaldi/7.1.3570.60',
    ];

    private static $userAgent = -1;

    public function setUp(): void
    {
        parent::setUp();

        // Setup the access layer
        FakeAccess::setIdSitesView([1, 2]);
        FakeAccess::setIdSitesAdmin([3, 4]);

        // Finally we set the user as a Super User by default
        FakeAccess::$superUser = true;

        // Needed to load the Intl_NumberFormatNumber translation string used when formatting the ranking numbers
        Fixture::loadAllTranslations();

        $this->randomiseUserAgent();
    }

    // Randomly select a user agent from the list above
    public static function randomiseUserAgent()
    {
        do {
            $ua = rand(0, count(self::$userAgents) - 1);
        } while ($ua === self::$userAgent);
        self::$userAgent = $ua;

        $_SERVER['HTTP_USER_AGENT'] = self::$userAgents[self::$userAgent];
    }

    /**
     * tell us when the API is broken
     */
    public function testAPI()
    {
        $dataTable = API::getInstance()->getRank('http://matomo.org/');
        $renderer = Renderer::factory('json');
        $renderer->setTable($dataTable);
        $ranks = json_decode($renderer->render(), true);
        foreach ($ranks as $rank) {
            if ($rank['rank'] == Piwik::translate('General_ErrorTryAgain')) {
                $this->markTestSkipped('An exception raised when fetching data. Skipping this test for now.');
                continue;
            }
            $this->assertNotEmpty($rank['rank'], $rank['id'] . ' expected non-zero rank, got [' . $rank['rank'] . ']');
        }
    }

    public function provideContainerConfig()
    {
        return [
          'Piwik\Access' => new FakeAccess()
        ];
    }
}
