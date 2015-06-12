<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class Plugins_SitesManagerTest
 *
 * @group Plugins
 */
class PrivacyManagerTest extends IntegrationTestCase
{
    /**
     * @var PrivacyManager
     */
    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new PrivacyManager();
        \Piwik\Option::set('delete_logs_enable', 1);
        \Piwik\Option::set('delete_logs_older_than', 270);
        \Piwik\Option::set('delete_reports_keep_week_reports', 1);
    }

    public function test_getPurgeDataSettings_shouldUseOnlyConfigValuesIfUIisDisabled()
    {
        $this->setUIEnabled(false);

        $settings = $this->manager->getPurgeDataSettings();
        $expected = $this->getDefaultPurgeSettings();

        $this->assertEquals($expected, $settings);
    }

    public function test_getPurgeDataSettings_shouldAlsoUseOptionValuesIfUIisEnabled()
    {
        $this->setUIEnabled(true);

        $settings = $this->manager->getPurgeDataSettings();
        $expected = $this->getDefaultPurgeSettings();

        $expected['delete_logs_enable'] = 1;
        $expected['delete_logs_older_than'] = 270;
        $expected['delete_reports_keep_week_reports'] = 1;

        $this->assertEquals($expected, $settings);
    }

    private function setUIEnabled($enabled)
    {
        \Piwik\Config::getInstance()->General['enable_delete_old_data_settings_admin'] = $enabled;
    }

    private function getDefaultPurgeSettings()
    {
        $expected = array(
            'delete_logs_enable' => 0,
            'delete_logs_schedule_lowest_interval' => 7,
            'delete_logs_older_than' => 180,
            'delete_logs_max_rows_per_query' => 100000,
            'enable_auto_database_size_estimate' => 1,
            'delete_reports_enable' => 0,
            'delete_reports_older_than' => 12,
            'delete_reports_keep_basic_metrics' => 1,
            'delete_reports_keep_day_reports' => 0,
            'delete_reports_keep_week_reports' => 0,
            'delete_reports_keep_month_reports' => 1,
            'delete_reports_keep_year_reports' => 1,
            'delete_reports_keep_range_reports' => 0,
            'delete_reports_keep_segment_reports' => 0,
        );
        return $expected;
    }

}
