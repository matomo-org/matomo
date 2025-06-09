<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\DataAccess\Model;
use Piwik\Date;
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

        $model = new Model();

        $invalidations = $model->getInvalidationsInProgress(
            $this->getIdSites(),
            $this->getProcessingHosts(),
            $startTime,
            $endTime
        );

        if (count($invalidations) === 0) {
            $this->getOutput()->writeln('No invalidations found.');
        } elseif ($dryRun) {
            $this->getOutput()->writeln(count($invalidations) . ' invalidations found:');
            if (count($invalidations) > 50) {
                $invalidations = array_slice($invalidations, 0, 50);
                $this->getOutput()->writeln('Output limited to oldest 50 records');
            }

            $header = [
                'name', 'idsite', 'report', 'date1', 'date2', 'period',
                'ts_invalidated', 'ts_started', 'processing_host', 'process_id'
            ];
            $rows = [];

            foreach ($invalidations as $invalidation) {
                $rows[] = [
                    'name' => $invalidation['name'],
                    'idsite' => $invalidation['idsite'],
                    'report' => $invalidation['report'],
                    'date1' => $invalidation['date1'],
                    'date2' => $invalidation['date2'],
                    'period' => $invalidation['period'],
                    'ts_invalidated' => $invalidation['ts_invalidated'],
                    'ts_started' => $invalidation['ts_started'],
                    'processing_host' => $invalidation['processing_host'],
                    'process_id' => $invalidation['process_id'],
                ];
            }

            $this->renderTable($header, $rows);
        } else {
            $rowCount = $model->releaseInProgressInvalidations(array_column($invalidations, 'idinvalidation'));

            $this->getOutput()->writeln('Number of invalidations that were reset: ' . $rowCount);
        }

        return self::SUCCESS;
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
