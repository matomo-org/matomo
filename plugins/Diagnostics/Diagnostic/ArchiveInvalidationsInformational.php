<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Db;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Information about the archive invalidations.
 */
class ArchiveInvalidationsInformational implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $results = [];

        if (SettingsPiwik::isMatomoInstalled()) {
            $invalidationCounts = $this->getInvalidationCounts();
            $results[] = DiagnosticResult::informationalResult('Total Invalidation Count', $invalidationCounts['all'] ?? '0');
            $results[] = DiagnosticResult::informationalResult('In Progress Invalidation Count', $invalidationCounts[ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS] ?? '0');
            $results[] = DiagnosticResult::informationalResult('Scheduled Invalidation Count', $invalidationCounts[ArchiveInvalidator::INVALIDATION_STATUS_QUEUED] ?? '0');

            $minMaxes = $this->getInvalidationMinMaxes();
            $results[] = DiagnosticResult::informationalResult('Earliest invalidation ts_started', $minMaxes['min_ts_started']);
            $results[] = DiagnosticResult::informationalResult('Latest invalidation ts_started', $minMaxes['max_ts_started']);
            $results[] = DiagnosticResult::informationalResult('Earliest invalidation ts_invalidated', $minMaxes['min_ts_invalidated']);
            $results[] = DiagnosticResult::informationalResult('Latest invalidation ts_invalidated', $minMaxes['max_ts_invalidated']);

            $invalidationTypes = $this->getInvalidationTypes();
            $results[] = DiagnosticResult::informationalResult('Number of segment invalidations', $invalidationTypes['count_segment']);
            $results[] = DiagnosticResult::informationalResult('Number of plugin invalidations', $invalidationTypes['count_plugin']);
            $results[] = DiagnosticResult::informationalResult('List of plugins being invalidated', implode(', ', $invalidationTypes['plugins']));
        }

        return $results;
    }

    public function getInvalidationCounts()
    {
        $table = Common::prefixTable('archive_invalidations');
        $sql = "SELECT COUNT(*) as `count`, status FROM `$table` GROUP BY status";

        $rows = Db::fetchAll($sql);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['status']]  = $row['count'];
        }
        $result['all'] = array_sum($result);
        return $result;
    }

    public function getInvalidationMinMaxes()
    {
        $sql = "SELECT MIN(ts_started) as min_ts_started, MAX(ts_started) as max_ts_started, MIN(ts_invalidated) as min_ts_invalidated, MAX(ts_invalidated) as max_ts_invalidated FROM "
            . Common::prefixTable('archive_invalidations');
        $row = Db::fetchRow($sql);
        return $row;
    }

    public function getInvalidationTypes()
    {
        $table = Common::prefixTable('archive_invalidations');

        $pluginSql = 'IF(INSTR(`name`, \'.\') > 0, SUBSTRING_INDEX(`name`, \'.\', -1), NULL)';
        $nonPluginDoneFlagSql = 'IF(INSTR(`name`, \'.\') > 0, SUBSTRING_INDEX(`name`, \'.\', 1), `name`)';

        $sql = "SELECT COUNT(*) as `count`, $pluginSql AS plugin, CHAR_LENGTH($nonPluginDoneFlagSql) > 32 AS is_segment_archive
                  FROM `$table`
              GROUP BY plugin, is_segment_archive";

        $result = [
            'count_segment' => 0,
            'count_plugin' => 0,
            'plugins' => [],
        ];

        $rows = Db::fetchAll($sql);
        foreach ($rows as $row) {
            if (!empty($row['is_segment_archive'])) {
                $result['count_segment'] += $row['count'];
            }

            if (!empty($row['plugin'])) {
                $result['count_plugin'] += $row['count'];
            }

            $result['plugins'][] = $row['plugin'];
        }

        $result['plugins'] = array_unique($result['plugins']);
        $result['plugins'] = array_filter($result['plugins']);
        $result['plugins'] = array_values($result['plugins']);

        return $result;
    }
}