<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreConsole\PerfCorpus\Formatter;
use Piwik\Plugins\CoreConsole\PerfCorpus\RunContext;
use Piwik\Plugins\CoreConsole\PerfCorpus\Verifier;

/**
 * Proves a generated corpus is actually correct before anything is measured against it.
 *
 * Exits non-zero on any failure, so it can gate a snapshot step in a script.
 */
class PerfCorpusVerify extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('perfcorpus:verify');
        $this->setDescription('Check a generated corpus and print a PASS/FAIL report.');

        $this->addRequiredValueOption('run-id', null, 'Which run to check. Defaults to the most recent.', null);
        $this->addRequiredValueOption('level', null, 'fast (reconciliation) or full (distributions too).', 'fast');
        $this->addRequiredValueOption('report', null, 'Also write the report as JSON to this path.', null);
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $level = $input->getOption('level');
        if (!in_array($level, [Verifier::LEVEL_FAST, Verifier::LEVEL_FULL], true)) {
            throw new \InvalidArgumentException('--level must be fast or full.');
        }

        $runId = $input->getOption('run-id');
        $context = RunContext::load(null === $runId || '' === $runId ? null : (int) $runId);

        $output->writeln('');
        $output->writeln(sprintf(
            '<info>perfcorpus:verify</info>  run %d  profile %s  seed %d  level %s',
            $context->getRunId(),
            $context->getProfile()->getName(),
            $context->getProfile()->getSeed(),
            $level
        ));
        $output->writeln('');

        $startedAt = microtime(true);
        $report = (new Verifier($context))->run($level);

        foreach ($report['checks'] as $check) {
            $output->writeln(sprintf(
                '  %s  %-58s %s',
                $check['passed'] ? '<info>PASS</info>' : '<error>FAIL</error>',
                $check['name'],
                $check['detail']
            ));
        }

        $failed = count(array_filter($report['checks'], static function (array $c): bool {
            return !$c['passed'];
        }));

        $output->writeln('');
        $output->writeln(sprintf(
            '  %d checks, %d failed, %s',
            count($report['checks']),
            $failed,
            Formatter::duration((int) round(microtime(true) - $startedAt))
        ));

        if ($report['passed']) {
            $this->writeSuccessMessage('Corpus verified.');
        } else {
            $output->writeln('');
            $output->writeln('<error>Corpus did NOT verify. Do not measure against it.</error>');
            $output->writeln('');
        }

        $reportPath = $input->getOption('report');
        if (!empty($reportPath)) {
            $payload = [
                'runId' => $context->getRunId(),
                'profile' => $context->getProfile()->toArray(),
                'gitCommit' => $context->getGitCommit(),
                'level' => $level,
                'verifiedAt' => gmdate('c'),
                'passed' => $report['passed'],
                'checks' => $report['checks'],
            ];

            file_put_contents($reportPath, json_encode($payload, JSON_PRETTY_PRINT) . "\n");
            $output->writeln('  report written to ' . $reportPath);
            $output->writeln('');
        }

        return $report['passed'] ? self::SUCCESS : self::FAILURE;
    }
}
