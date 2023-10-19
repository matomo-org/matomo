<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Plugin\Manager;
use Piwik\Widget\WidgetConfig;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Widget\WidgetsList;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class WidgetsListTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;
    }

    public function testGet()
    {
        Fixture::createWebsite('2009-01-04 00:11:42');

        $_GET['idSite'] = 1;

        $widgets = WidgetsList::get();

        $widgetsPerCategory = $this->getWidgetsPerCategory($widgets);

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'Dashboard_Dashboard' => 1,
            'General_Actions' => 26,
            'General_KpiMetric' => 1,
            'General_Visitors' => 30,
            'SEO' => 1,
            'Goals_Goals' => 3,
            'Insights_WidgetCategory' => 2,
            'ExampleUI_UiFramework' => 8,
            'Referrers_Referrers' => 10,
            'About Matomo' => 11,
            'Marketplace_Marketplace' => 3,

            // widgets provided by Professional Services plugin for plugin promos
            'ProfessionalServices_PromoAbTesting' => 1,
            'ProfessionalServices_PromoCrashAnalytics' => 1,
            'ProfessionalServices_PromoCustomReports' => 1,
            'ProfessionalServices_PromoFormAnalytics' => 1,
            'ProfessionalServices_PromoFunnels' => 1,
            'ProfessionalServices_PromoHeatmaps' => 1,
            'ProfessionalServices_PromoMediaAnalytics' => 1,
            'ProfessionalServices_PromoSessionRecording' => 1,
        );

        if (Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $numberOfWidgets['General_Visitors']++;
        }

        // number of main categories
        $this->assertEquals(count($numberOfWidgets), count($widgetsPerCategory));

        foreach ($numberOfWidgets as $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($widgetsPerCategory[$category]), sprintf("Widget: %s", $category));
        }
    }

    private function getWidgetsPerCategory(WidgetsList $list)
    {
        $widgetsPerCategory = array();
        foreach ($list->getWidgetConfigs() as $widgetConfig) {
            $category = $widgetConfig->getCategoryId();
            if (!isset($widgetsPerCategory[$category])) {
                $widgetsPerCategory[$category] = array();
            }

            $widgetsPerCategory[$category][] = $widgetConfig;
        }

        return $widgetsPerCategory;
    }

    public function testGetWithGoals()
    {
        Fixture::createWebsite('2009-01-04 00:11:42');

        $initialGoalsWidgets = 3;

        $_GET['idSite'] = 1;

        $perCategory = $this->getWidgetsPerCategory(WidgetsList::get());
        $this->assertEquals($initialGoalsWidgets, count($perCategory['Goals_Goals']));


        API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $perCategory = $this->getWidgetsPerCategory(WidgetsList::get());

        // number of main categories
        $this->assertEquals(19, count($perCategory));
        $this->assertEquals($initialGoalsWidgets + 2, count($perCategory['Goals_Goals'])); // make sure widgets for that goal were added
    }

    public function testGetWithGoalsAndEcommerce()
    {
        Fixture::createWebsite('2009-01-04 00:11:42', true);
        API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        $perCategory = $this->getWidgetsPerCategory(WidgetsList::get());

        // number of main categories
        $this->assertEquals(20, count($perCategory));

        // check if each category has the right number of widgets
        $numberOfWidgets = array(
            'Goals_Goals'     => 5,
            'Goals_Ecommerce' => 4,
        );

        foreach ($numberOfWidgets as $category => $widgetCount) {
            $this->assertEquals($widgetCount, count($perCategory[$category]));
        }
    }

    public function testRemove()
    {
        Fixture::createWebsite('2009-01-04 00:11:42', true);
        API::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);

        $_GET['idSite'] = 1;

        $list = WidgetsList::get();

        $this->assertCount(20, $this->getWidgetsPerCategory($list));

        $list->remove('SEO', 'NoTeXiStInG');

        $perCategory = $this->getWidgetsPerCategory($list);
        $this->assertCount(20, $perCategory);

        $this->assertArrayHasKey('SEO', $perCategory);
        $this->assertCount(1, $perCategory['SEO']);

        $list->remove('SEO', 'SEO_SeoRankings');

        $perCategory = $this->getWidgetsPerCategory($list);
        $this->assertArrayNotHasKey('SEO', $perCategory);
        $this->assertArrayHasKey('About Matomo', $perCategory);

        $list->remove('About Matomo');

        $perCategory = $this->getWidgetsPerCategory($list);
        $this->assertArrayNotHasKey('About Matomo', $perCategory);
    }

    public function testIsDefined()
    {
        Fixture::loadAllTranslations();

        Fixture::createWebsite('2009-01-04 00:11:42', true);

        $_GET['idSite'] = 1;

        $config = new WidgetConfig();
        $config->setCategoryId('Actions');
        $config->setName('Pages');
        $config->setModule('Actions');
        $config->setAction('getPageUrls');
        $list = WidgetsList::get();
        $list->addWidgetConfig($config);

        $this->assertTrue($list->isDefined('Actions', 'getPageUrls'));
        $this->assertFalse($list->isDefined('Actions', 'inValiD'));

        Fixture::resetTranslations();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
