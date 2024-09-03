<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Config;
use Piwik\Config\DatabaseConfig;
use Piwik\Config\GeneralConfig;
use Piwik\Plugin\ConsoleCommand;

/**
 * Diagnostic command that returns current configuration settings for archiving
 */
class ArchivingConfig extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:archiving-config');
        $this->addNoValueOption(
            'json',
            null,
            "If supplied, the command will return data in json format"
        );
        $this->setDescription('Show configuration settings that can affect archiving performance');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $metrics = $this->getArchivingConfig();

        if ($input->getOption('json')) {
            $output->write(json_encode($metrics));
        } else {
            $headers = ['Section', 'Setting', 'Value'];
            $this->renderTable($headers, $metrics);
        }

        return self::SUCCESS;
    }

    /**
     * Retrieve various data statistics useful for diagnosing archiving performance
     *
     * @return array
     */
    public function getArchivingConfig(): array
    {
        $configs = [
            'database' => [
                'enable_segment_first_table_join_prefix',
                'enable_first_table_join_prefix'
                ],
            'general' => [
                'browser_archiving_disabled_enforce',
                'enable_processing_unique_visitors_day',
                'enable_processing_unique_visitors_week',
                'enable_processing_unique_visitors_month',
                'enable_processing_unique_visitors_year',
                'enable_processing_unique_visitors_range',
                'enable_processing_unique_visitors_multiple_sites',
                'process_new_segments_from',
                'time_before_today_archive_considered_outdated',
                'time_before_week_archive_considered_outdated',
                'time_before_month_archive_considered_outdated',
                'time_before_year_archive_considered_outdated',
                'time_before_range_archive_considered_outdated',
                'enable_browser_archiving_triggering',
                'archiving_range_force_on_browser_request',
                'archiving_custom_ranges[]',
                'archiving_query_max_execution_time',
                'archiving_ranking_query_row_limit',
                'disable_archiving_segment_for_plugins',
                'disable_archive_actions_goals',
                'datatable_archiving_maximum_rows_referrers',
                'datatable_archiving_maximum_rows_subtable_referrers',
                'datatable_archiving_maximum_rows_userid_users',
                'datatable_archiving_maximum_rows_custom_dimensions',
                'datatable_archiving_maximum_rows_subtable_custom_dimensions',
                'datatable_archiving_maximum_rows_actions',
                'datatable_archiving_maximum_rows_subtable_actions',
                'datatable_archiving_maximum_rows_site_search',
                'datatable_archiving_maximum_rows_events',
                'datatable_archiving_maximum_rows_subtable_events',
                'datatable_archiving_maximum_rows_products',
                'datatable_archiving_maximum_rows_standard'
                ]
            ];

        $data = [];
        foreach ($configs as $section => $sectionConfigs) {
            foreach ($sectionConfigs as $setting) {
                switch ($section) {
                    case 'general':
                        $value = GeneralConfig::getConfigValue($setting);
                        break;
                    case 'database':
                        $value = DatabaseConfig::getConfigValue($setting);
                        break;
                    default:
                        $value = Config::getInstance()->{$section}[$setting] ?? '';
                }
                $data[] = ['Section' => $section, 'Setting' => $setting, 'Value' => $value];
            }
        }
        return $data;
    }
}
