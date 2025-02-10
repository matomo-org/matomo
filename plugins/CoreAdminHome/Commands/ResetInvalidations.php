<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;

class ResetInvalidations extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:reset-invalidations');
        $this->setDescription('Resets invalidations that are stuck in the "in progress" state, allowing them to be reprocessed.');

        $this->addRequiredValueOption(
            'processing-host',
            null,
            'Restrict the reset to invalidations assigned to the specified host. Can be used multiple times to target multiple hosts.',
            null,
            true
        );

        $this->addRequiredValueOption(
            'idsite',
            null,
            'Specify the site ID for which invalidations should be reset. Can be used multiple times to target multiple sites.',
            null,
            true
        );

        $this->addRequiredValueOption(
            'older-than',
            null,
            'Only reset invalidations that were started before the given time. Accepts any date format parsable by `strtotime` (e.g. "1 day ago", "2024-01-01 12:00:00").'
        );

        $this->addRequiredValueOption(
            'newer-than',
            null,
            'Only reset invalidations that were started after the given time. Accepts any date format parsable by `strtotime` (e.g. "1 hour ago", "2024-02-01").'
        );

        $this->addNoValueOption(
            'dry-run',
            null,
            'Perform a dry run without making changes. Shows which invalidations would be reset without actually modifying them.'
        );

        $this->setHelp(
            'This command allows administrators to reset stuck invalidations that are incorrectly marked as "in progress". '
            . 'This can happen if an archiving process was interrupted, such as during a server crash or a deployment, leaving '
            . 'invalidations in a stuck state. Resetting them ensures they can be reprocessed in the next archiving run.

⚠  Warning: Only reset invalidations when you are certain they are no longer being processed. ⚠

Resetting active invalidations can lead to incomplete archives, data inconsistencies and wasted processing resources.

Usage examples:

- Reset all stuck invalidations for site ID 1 that were started more than an hour ago:
  `./console core:reset-invalidations --idsite=1 --older-than="1 hour ago"`

- Reset invalidations assigned to a specific host:
  `./console core:reset-invalidations --processing-host=archiver1.example.com`

- Perform a dry run to check which invalidations would be reset:
  `./console core:reset-invalidations --idsite=1 --older-than="1 hour ago" --dry-run`

- Reset invalidations for multiple sites and hosts:
  `./console core:reset-invalidations --idsite=1 --idsite=10 --processing-host=archiver1 --processing-host=archiver2`

Use this command with caution, especially when resetting invalidations while archiving processes are still in progress.'
        );
    }

    protected function doExecute(): int
    {
        $dryRun = $this->getInput()->getOption('dry-run');

        $whereCondition = $this->generateWhereCondition();

        if ($dryRun) {
            $rows = Db::fetchAll(
                'SELECT name, idsite, report, date1, date2, period, ts_invalidated, ts_started, processing_host, process_id FROM '
                . Common::prefixTable('archive_invalidations') . ' WHERE ' . $whereCondition['sql']
                . ' ORDER BY ts_started',
                $whereCondition['bind']
            );

            if (count($rows) === 0) {
                $this->getOutput()->writeln('No invalidations found.');
            } else {
                $this->getOutput()->writeln(count($rows) . ' invalidations found:');
                if (count($rows) > 50) {
                    $rows = array_slice($rows, 0, 50);
                    $this->getOutput()->writeln('Output limited to oldest 50 records');
                }
                $this->renderTable(array_keys($rows[0]), $rows);
            }
        } else {
            $queryObj = Db::query(
                'UPDATE ' . Common::prefixTable('archive_invalidations')
                    . ' SET status = 0, processing_host = NULL, process_id = NULL, ts_started = NULL WHERE '
                    . $whereCondition['sql'],
                $whereCondition['bind']
            );
            $rowCount = $queryObj->rowCount();

            $this->getOutput()->writeln('Number of invalidations that were reset: ' . $rowCount);
        }

        return self::SUCCESS;
    }

    private function generateWhereCondition(): array
    {
        $whereConditions = [];
        $binds = [];
        $processingHosts = $this->getProcessingHosts();
        $idSites = $this->getIdSites();
        try {
            $startTime = $this->getStartTime();
        } catch (\Exception $e) {
            throw new \Exception('Invalid value for --newer-than provided.', $e->getCode(), $e);
        }

        try {
            $endTime = $this->getEndTime();
        } catch (\Exception $e) {
            throw new \Exception('Invalid value for --older-than provided.', $e->getCode(), $e);
        }

        $whereConditions[] = '`status` = ?';
        $binds[] = ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS;

        if (!empty($processingHosts)) {
            $whereConditions[] = sprintf('`processing_host` IN (%1$s)', Common::getSqlStringFieldsArray($processingHosts));
            $binds = array_merge($binds, $processingHosts);
        }

        if (!empty($idSites)) {
            $whereConditions[] = sprintf('`idsite` IN (' . implode(', ', $idSites) . ')');
        }

        if (!empty($startTime)) {
            $whereConditions[] = '`ts_started` > ?';
            $binds[] = $startTime->toString('Y-m-d H:i:s');
        }

        if (!empty($endTime)) {
            $whereConditions[] = '`ts_started` < ?';
            $binds[] = $endTime->toString('Y-m-d H:i:s');
        }

        return [
            'sql' => implode(' AND ', $whereConditions),
            'bind' => $binds,
        ];
    }

    private function getProcessingHosts(): array
    {
        $processingHosts = $this->getInput()->getOption('processing-host');

        return !is_array($processingHosts) ? [$processingHosts] : $processingHosts;
    }

    private function getIdSites(): array
    {
        $idSites = $this->getInput()->getOption('idsite');

        return Site::getIdSitesFromIdSitesString($idSites);
    }

    private function getStartTime(): ?Date
    {
        $startTime = $this->getInput()->getOption('newer-than');

        if (empty($startTime)) {
            return null;
        }

        return Date::factory($startTime);
    }

    private function getEndTime(): ?Date
    {
        $endTime = $this->getInput()->getOption('older-than');

        if (empty($endTime)) {
            return null;
        }

        return Date::factory($endTime);
    }
}
