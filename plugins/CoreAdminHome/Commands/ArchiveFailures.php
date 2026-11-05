<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\DataAccess\ArchiveFailures as ArchiveFailuresDao;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;

class ArchiveFailures extends ConsoleCommand
{
    private const MAX_ROWS_TO_LIST = 50;
    private const MAX_ERROR_LENGTH_TO_LIST = 100;

    protected function configure()
    {
        $this->setName('core:archive-failures');
        $this->setDescription('Lists archives that core:archive failed to process, and resets them so they are retried in the next run.');

        $this->addRequiredValueOption(
            'idsite',
            null,
            'Only include failed archives of the specified site. Can be used multiple times to target multiple sites.',
            null,
            true
        );

        $this->addNoValueOption(
            'given-up',
            null,
            'Only include failed archives that are no longer retried, because they reached the maximum number of attempts.'
        );

        $this->addNoValueOption(
            'reset',
            null,
            'Reset the failed archives matching the other options, so core:archive processes them again in its next run.'
        );

        $this->setHelp(
            'When archiving fails, core:archive keeps the invalidation, but skips it, along with the periods built from it, '
            . 'for a while before retrying (see [General] archive_failure_retry_delays). After [General] archive_failure_max_attempts '
            . 'failed attempts it is no longer retried until it is reset with this command.

Usage examples:

- List all failed archives:
  `./console core:archive-failures`

- List the failed archives of site ID 1 that are no longer retried:
  `./console core:archive-failures --idsite=1 --given-up`

- Reset all failed archives of site ID 1, so they are retried in the next core:archive run:
  `./console core:archive-failures --idsite=1 --reset`

- Reset all failed archives:
  `./console core:archive-failures --reset`'
        );
    }

    protected function doExecute(): int
    {
        $archiveFailures = new ArchiveFailuresDao();
        $idSites = $this->getIdSites();
        $givenUpOnly = (bool) $this->getInput()->getOption('given-up');

        if ($this->getInput()->getOption('reset')) {
            $count = 0;
            $idFailures = [];
            foreach ($archiveFailures->iterateFailures($idSites, $givenUpOnly, false) as $failure) {
                $idFailures[] = $failure['idfailure'];
                if (count($idFailures) >= 1000) {
                    $count += $archiveFailures->deleteFailuresById($idFailures);
                    $idFailures = [];
                }
            }
            $count += $archiveFailures->deleteFailuresById($idFailures);

            if ($count === 0) {
                $this->getOutput()->writeln('No failed archives found.');
            } else {
                $this->getOutput()->writeln("Number of failed archives that were reset: $count. They will be processed in the next core:archive run.");
            }

            return self::SUCCESS;
        }

        $count = 0;
        $failures = [];
        foreach ($archiveFailures->iterateFailures($idSites, $givenUpOnly) as $failure) {
            ++$count;
            if (count($failures) < self::MAX_ROWS_TO_LIST) {
                $failures[] = $failure;
            }
        }

        if ($count === 0) {
            $this->getOutput()->writeln('No failed archives found.');
            return self::SUCCESS;
        }

        $this->getOutput()->writeln($count . ' failed archives found:');
        if ($count > self::MAX_ROWS_TO_LIST) {
            $this->getOutput()->writeln('Output limited to the oldest ' . self::MAX_ROWS_TO_LIST . ' records');
        }

        $periods = array_flip(Piwik::$idPeriods);
        $header = ['idsite', 'period', 'date1', 'date2', 'name', 'report', 'attempts', 'state', 'retry after (UTC)', 'last failed (UTC)', 'last error'];
        $rows = [];

        foreach ($failures as $failure) {
            $error = (string) $failure['last_error'];
            if (mb_strlen($error) > self::MAX_ERROR_LENGTH_TO_LIST) {
                $error = mb_substr($error, 0, self::MAX_ERROR_LENGTH_TO_LIST) . '...';
            }

            $rows[] = [
                $failure['idsite'],
                $periods[$failure['period']] ?? $failure['period'],
                $failure['date1'],
                $failure['date2'],
                $failure['name'],
                $failure['report'],
                $failure['attempts'],
                $failure['state'],
                $failure['state'] === ArchiveFailuresDao::STATE_GIVEN_UP ? '' : $failure['ts_retry_after'],
                $failure['ts_last_failed'],
                $error,
            ];
        }

        $this->renderTable($header, $rows);

        return self::SUCCESS;
    }

    private function getIdSites(): array
    {
        $idSites = $this->getInput()->getOption('idsite');

        if (empty($idSites)) {
            return [];
        }

        // an invalid value must not widen the selection to all sites
        try {
            $idSites = Site::getIdSitesFromIdSitesString($idSites, false, true);
        } catch (\Exception $e) {
            $idSites = [];
        }

        if (empty($idSites)) {
            throw new \Exception('Invalid value for --idsite provided.');
        }

        return $idSites;
    }
}
