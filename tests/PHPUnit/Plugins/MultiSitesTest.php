<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class MultiSitesTest extends DatabaseTestCase
{
    protected $idSiteAccess;

    public function setUp()
    {
        parent::setUp();

        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);

        $this->idSiteAccess = Piwik_SitesManager_API::getInstance()->addSite("test", "http://test");

        Piwik_PluginsManager::getInstance()->loadPlugins(array('MultiSites', 'VisitsSummary', 'Actions'));
        Piwik_PluginsManager::getInstance()->installLoadedPlugins();
    }


    /**
     * Testing that getOne returns a row even when there are no data
     * This is necessary otherwise Piwik_API_ResponseBuilder throws 'Call to a member function getColumns() on a non-object'
     *
     * @group Plugins
     * @group MultiSites
     */
    public function testWhenNoDataGetOneReturnsRow()
    {
        $dataTable = Piwik_MultiSites_API::getInstance()->getOne($this->idSiteAccess, 'month', '01-01-2010');
        $this->assertEquals(1, $dataTable->getRowsCount());

        // safety net
        $this->assertEquals(0, $dataTable->getFirstRow()->getColumn('nb_visits'));
    }
}
