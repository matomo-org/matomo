<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreConsole\PerfCorpus\SyncVerifier;

/**
 * Checks that the analytics database holds what MySQL holds, after tracking write load.
 *
 * perfcorpus:churn reproduces the write load and reports the statement mix the tracker produced.
 * It never looks at the other side, so on its own it cannot tell you whether the replication or
 * change-data-capture pipeline kept up, or - the interesting case - whether it delivered the
 * INSERTs and dropped the UPDATEs. Run this straight after a churn run.
 *
 * Exits non-zero on any failure, so it can gate a step in a script.
 */
class PerfCorpusVerifySync extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('perfcorpus:verify-sync');
        $this->setDescription(
            'Check that the analytics database holds what MySQL holds, after a churn run.'
        );
        $this->setHelp(<<<'HELP'
Compares the two copies Matomo would actually read, in increasing order of what it proves:

  watermarks     MAX(primary key) each side. The gap is replication lag, in rows. Not COUNT(*) -
                 on a billion-row InnoDB table that is a full scan and does not return.
  recent window  the same over the last --window minutes only, which separates "behind on
                 everything" from "caught up on history but not keeping up now".
  updates        (--level=full) a sample of visits MySQL changed inside the window, compared
                 column by column. This is the one that matters: an insert-only pipeline passes
                 the first two and fails this, and it fails it silently, because the row is
                 present and merely stale.

<comment>Examples</comment>

  # straight after a 30 minute churn run
  ./console perfcorpus:verify-sync --level=full --window=45

  # wait up to 10 minutes for the pipe to drain first
  ./console perfcorpus:verify-sync --level=full --window=45 --wait=600
HELP);

        $this->addRequiredValueOption('level', null, 'fast (watermarks and counts) or full (also compares changed rows).', SyncVerifier::LEVEL_FULL);
        $this->addRequiredValueOption('window', null, 'How many minutes back the recent-window and sample checks look.', 120);
        $this->addRequiredValueOption('sample', null, 'How many changed visits to compare column by column.', 200);
        $this->addRequiredValueOption('wait', null, 'Seconds to keep re-checking while anything is still behind, before reporting. 0 reports immediately.', 0);
        $this->addRequiredValueOption('json', null, 'Also write the result to this path as JSON.', '');
    }

    protected function doExecute(): int
    {
        $output = $this->getOutput();
        $level = (string) $this->getInput()->getOption('level');
        $window = (int) $this->getInput()->getOption('window');
        $sample = (int) $this->getInput()->getOption('sample');
        $wait = (int) $this->getInput()->getOption('wait');
        $json = (string) $this->getInput()->getOption('json');

        $verifier = new SyncVerifier();

        // A pipe that is merely behind will catch up, and reporting a failure the moment churn
        // stops measures the drain rather than the pipeline. Re-check until it settles.
        $deadline = time() + $wait;
        do {
            $result = $verifier->run($level, $window, $sample);
            $settled = $result['passed'] && !$this->hasWarnings($result);
            if ($settled || time() >= $deadline) {
                break;
            }
            $output->writeln('<comment>not settled yet, re-checking in 30s...</comment>');
            sleep(30);
        } while (true);

        $output->writeln('');
        foreach ($result['checks'] as $check) {
            $output->writeln(sprintf(
                '  %s  %-34s %s',
                $this->tag($check['status']),
                $check['check'],
                $check['detail']
            ));
        }
        $output->writeln('');
        $output->writeln($result['passed']
            ? '<info>PASS</info> - the analytics copy holds what MySQL holds.'
            : '<error>FAIL</error> - see the FAIL lines above.');

        if ($json !== '') {
            file_put_contents($json, (string) json_encode($result, JSON_PRETTY_PRINT));
            $output->writeln('Wrote ' . $json);
        }

        return $result['passed'] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param array{passed: bool, checks: array<int, array<string, mixed>>} $result
     */
    private function hasWarnings(array $result): bool
    {
        foreach ($result['checks'] as $check) {
            if ($check['status'] === 'WARN') {
                return true;
            }
        }

        return false;
    }

    private function tag(string $status): string
    {
        if ($status === 'PASS') {
            return '<info>PASS</info>';
        }

        return $status === 'WARN' ? '<comment>WARN</comment>' : '<error>FAIL</error>';
    }
}
