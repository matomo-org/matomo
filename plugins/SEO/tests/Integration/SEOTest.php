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

        // Google and Bing may not show the indexed pages count for some user agents, some UA strings will work for
        // Google, but not Bing and visa-versa. This user agent string works for both as of 2023-06-26:
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36';
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
