<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

        Test_Piwik_BaseFixture::createWebsite('2009-01-04 00:11:42');

        $_GET['idSite'] = 1;

        IntegrationTestCase::loadAllPlugins();

        Piwik_WidgetsList::_reset();
        $widgets = Piwik_GetWidgetsList();
        Piwik_WidgetsList::_reset();

        // number of main categories
        $this->assertEquals(12, count($widgets));

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'VisitsSummary_VisitsSummary'  => 6,
            'Live!'                        => 3,
            'General_Visitors'             => 12,
            'UserSettings_VisitorSettings' => 11,
            'Actions_Actions'              => 8,
            'Actions_SubmenuSitesearch'    => 5,
            'Referers_Referers'            => 7,
            'Goals_Goals'                  => 1,
            'SEO'                          => 2,
            'Example Widgets'              => 4,
            'DevicesDetection_DevicesDetection' => 7,
            'ExamplePlugin_exampleWidgets' => 3
        );
        foreach ($numberOfWidgets AS $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]), sprintf("Widget: %s", $category));
        }
        IntegrationTestCase::unloadAllPlugins();
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

        Test_Piwik_BaseFixture::createWebsite('2009-01-04 00:11:42');
        Piwik_Goals_API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        IntegrationTestCase::loadAllPlugins();

        Piwik_WidgetsList::_reset();
        $widgets = Piwik_GetWidgetsList();
        Piwik_WidgetsList::_reset();

        // number of main categories
        $this->assertEquals(12, count($widgets));

        // check that the goal widget was added
        $numberOfWidgets = array(
            'Goals_Goals' => 2,
        );

        foreach ($numberOfWidgets AS $category => $widgetCount) {
            $expected = count($widgets[$category]);
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

        Test_Piwik_BaseFixture::createWebsite('2009-01-04 00:11:42', true);
        Piwik_Goals_API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        IntegrationTestCase::loadAllPlugins();

        Piwik_WidgetsList::_reset();
        $widgets = Piwik_GetWidgetsList();
        Piwik_WidgetsList::_reset();

        // number of main categories
        $this->assertEquals(13, count($widgets));

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'Goals_Goals'     => 2,
            'Goals_Ecommerce' => 5,
        );

        foreach ($numberOfWidgets AS $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]));
        }
    }


}
