<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\tests\Integration;

use Piwik\Access;
use Piwik\FrontController;
use Piwik\Plugins\MultiSites\API as APIMultiSites;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class Plugins_MultiSitesTest
 *
 * @group Plugins
 */
class MultiSitesTest extends IntegrationTestCase
{
    protected $idSiteAccess;

    public function setUp(): void
    {
        parent::setUp();

        $access = Access::getInstance();
        $access->setSuperUserAccess(true);

        $this->idSiteAccess = APISitesManager::getInstance()->addSite("test", "http://test");

        \Piwik\Plugin\Manager::getInstance()->loadPlugins(array('MultiSites', 'VisitsSummary', 'Actions'));
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();
    }

    /**
     * Testing that getOne returns a row even when there are no data
     * This is necessary otherwise ResponseBuilder throws 'Call to a member function getColumns() on a non-object'
     *
     * @group Plugins
     */
    public function testWhenNoDataGetOneReturnsRow()
    {
        $dataTable = APIMultiSites::getInstance()->getOne($this->idSiteAccess, 'month', '01-01-2010');
        $this->assertEquals(1, $dataTable->getRowsCount());

        // safety net
        $this->assertEquals(0, $dataTable->getFirstRow()->getColumn('nb_visits'));
    }

    /**
     * Testing that getOne does not error out when format=rss, #10407
     *
     * @group Plugins
     */
    public function testWhenRssFormatGetOneDoesNotError()
    {
        $_GET = array(
            'method' => 'MultiSites.getOne',
            'idSite' => $this->idSiteAccess,
            'period' => 'month',
            'date'   => 'last10',
            'format'   => 'rss'
        );

        $output = FrontController::getInstance()->fetchDispatch('API');

        self::assertStringContainsString('<item>', $output);
        self::assertStringContainsString('</rss>', $output);
        self::assertStringNotContainsString('error', $output);

        $_GET = array();
    }
}
