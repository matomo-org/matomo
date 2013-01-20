<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

class WidgetsListTest extends DatabaseTestCase
{
    /**
     * @group Core
     * @group PluginsFunctions
     * @group WidgetsList
     */
    public function testGet()
    {
        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Zend_Registry::set('access', $pseudoMockAccess);

        IntegrationTestCase::createWebsite('2009-01-04 00:11:42');

        $_GET['idSite'] = 1;

        $pluginsManager = Piwik_PluginsManager::getInstance();
        $pluginsToLoad  = Piwik_Config::getInstance()->Plugins['Plugins'];
        $pluginsManager->loadPlugins($pluginsToLoad);

        Piwik_WidgetsList::_reset();
        $widgets = Piwik_GetWidgetsList();
        Piwik_WidgetsList::_reset();

        // there should be 11 main categories
        $this->assertEquals(11, count($widgets));

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'VisitsSummary_VisitsSummary'  => 6,
            'Live!'                        => 2,
            'General_Visitors'             => 12,
            'UserSettings_VisitorSettings' => 10,
            'Actions_Actions'              => 8,
            'Actions_SubmenuSitesearch'    => 5,
            'Referers_Referers'            => 6,
            'Goals_Goals'                  => 1,
            'SEO'                          => 2,
            'Example Widgets'              => 3,
            'ExamplePlugin_exampleWidgets' => 3
        );

        foreach ($numberOfWidgets AS $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]));
        }
    }

    /**
     * @group Core
     * @group PluginsFunctions
     * @group WidgetsList
     */
    public function testGetWithGoals()
    {
        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Zend_Registry::set('access', $pseudoMockAccess);

        IntegrationTestCase::createWebsite('2009-01-04 00:11:42');
        Piwik_Goals_API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        $pluginsManager = Piwik_PluginsManager::getInstance();
        $pluginsToLoad  = Piwik_Config::getInstance()->Plugins['Plugins'];
        $pluginsManager->loadPlugins($pluginsToLoad);

        Piwik_WidgetsList::_reset();
        $widgets = Piwik_GetWidgetsList();
        Piwik_WidgetsList::_reset();

        // there should be 11 main categories
        $this->assertEquals(11, count($widgets));

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'VisitsSummary_VisitsSummary'  => 6,
            'Live!'                        => 2,
            'General_Visitors'             => 12,
            'UserSettings_VisitorSettings' => 10,
            'Actions_Actions'              => 8,
            'Actions_SubmenuSitesearch'    => 5,
            'Referers_Referers'            => 6,
            'Goals_Goals'                  => 2,
            'SEO'                          => 2,
            'Example Widgets'              => 3,
            'ExamplePlugin_exampleWidgets' => 3
        );

        foreach ($numberOfWidgets AS $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]));
        }
    }

    /**
     * @group Core
     * @group PluginsFunctions
     * @group WidgetsList
     */
    public function testGetWithGoalsAndEcommerce()
    {
        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Zend_Registry::set('access', $pseudoMockAccess);

        IntegrationTestCase::createWebsite('2009-01-04 00:11:42', true);
        Piwik_Goals_API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        $pluginsManager = Piwik_PluginsManager::getInstance();
        $pluginsToLoad  = Piwik_Config::getInstance()->Plugins['Plugins'];
        $pluginsManager->loadPlugins($pluginsToLoad);

        Piwik_WidgetsList::_reset();
        $widgets = Piwik_GetWidgetsList();
        Piwik_WidgetsList::_reset();

        // there should be 12 main categories
        $this->assertEquals(12, count($widgets));

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'VisitsSummary_VisitsSummary'  => 6,
            'Live!'                        => 2,
            'General_Visitors'             => 12,
            'UserSettings_VisitorSettings' => 10,
            'Actions_Actions'              => 8,
            'Actions_SubmenuSitesearch'    => 5,
            'Referers_Referers'            => 6,
            'Goals_Goals'                  => 2,
            'Goals_Ecommerce'              => 5,
            'SEO'                          => 2,
            'Example Widgets'              => 3,
            'ExamplePlugin_exampleWidgets' => 3
        );

        foreach ($numberOfWidgets AS $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]));
        }
    }

}
