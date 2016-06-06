<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Translate;
use Piwik\WidgetsList;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group WidgetsListTest
 * @group Core
 */
class WidgetsListTest extends IntegrationTestCase
{
    public function testGet()
    {
        // setup the access layer
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2009-01-04 00:11:42');

        $_GET['idSite'] = 1;

        WidgetsList::_reset();
        $widgets = WidgetsList::get();
        WidgetsList::_reset();

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'VisitsSummary_VisitsSummary'  => 6,
            'Live!'                        => 4,
            'General_Visitors'             => 11,
            'General_VisitorSettings'      => 5,
            'General_Actions'              => 10,
            'Events_Events'                => 3,
            'Actions_SubmenuSitesearch'    => 5,
            'Referrers_Referrers'          => 7,
            'Goals_Goals'                  => 1,
            'SEO'                          => 2,
            'About Piwik'                  => 6,
            'DevicesDetection_DevicesDetection' => 8,
            'Insights_WidgetCategory' => 2
        );

        // number of main categories
        $this->assertEquals(count($numberOfWidgets), count($widgets));

        foreach ($numberOfWidgets as $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]), sprintf("Widget: %s", $category));
        }
    }

    public function testGetWithGoals()
    {
        // setup the access layer
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2009-01-04 00:11:42');
        API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        WidgetsList::_reset();
        $widgets = WidgetsList::get();
        WidgetsList::_reset();

        // number of main categories
        $this->assertEquals(13, count($widgets));

        // check that the goal widget was added
        $numberOfWidgets = array(
            'Goals_Goals' => 2,
        );

        foreach ($numberOfWidgets as $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]));
        }
    }

    public function testGetWithGoalsAndEcommerce()
    {
        // setup the access layer
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2009-01-04 00:11:42', true);
        API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        WidgetsList::_reset();
        $widgets = WidgetsList::get();
        WidgetsList::_reset();

        // number of main categories
        $this->assertEquals(14, count($widgets));

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'Goals_Goals'     => 2,
            'Goals_Ecommerce' => 5,
        );

        foreach ($numberOfWidgets as $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgets[$category]));
        }
    }

    public function testRemove()
    {
        // setup the access layer
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2009-01-04 00:11:42', true);
        API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        WidgetsList::_reset();
        $widgets = WidgetsList::get();

        $this->assertCount(14, $widgets);
        WidgetsList::remove('SEO', 'NoTeXiStInG');

        $widgets = WidgetsList::get();
        $this->assertCount(14, $widgets);

        $this->assertArrayHasKey('SEO', $widgets);
        $this->assertCount(2, $widgets['SEO']);

        WidgetsList::remove('SEO', 'SEO_SeoRankings');
        $widgets = WidgetsList::get();

        $this->assertCount(1, $widgets['SEO']);

        WidgetsList::remove('SEO');
        $widgets = WidgetsList::get();

        $this->assertArrayNotHasKey('SEO', $widgets);

        WidgetsList::_reset();
    }

    public function testIsDefined()
    {
        // setup the access layer
        FakeAccess::$superUser = true;

        Translate::loadAllTranslations();

        Fixture::createWebsite('2009-01-04 00:11:42', true);

        $_GET['idSite'] = 1;

        WidgetsList::_reset();
        WidgetsList::add('Actions', 'Pages', 'Actions', 'getPageUrls');

        $this->assertTrue(WidgetsList::isDefined('Actions', 'getPageUrls'));
        $this->assertFalse(WidgetsList::isDefined('Actions', 'inValiD'));

        Translate::reset();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
