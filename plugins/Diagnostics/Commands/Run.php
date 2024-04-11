<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Piwik\Plugins\Diagnostics\DiagnosticService;

/**
 * Run the diagnostics.
 */
class Run extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:run')
            ->setDescription('Run diagnostics to check that Piwik is installed and runs correctly')
            ->addNoValueOption('all', null, 'Show all diagnostics, including those that passed with success');
    }

    protected function doExecute(): int
    {
        // Replace this with dependency injection once available
        /** @var DiagnosticService $diagnosticService */
        $diagnosticService = StaticContainer::get('Piwik\Plugins\Diagnostics\DiagnosticService');

        $input = $this->getInput();
        $output = $this->getOutput();
        $showAll = $input->getOption('all');

        $report = $diagnosticService->runDiagnostics();

        foreach ($report->getAllResults() as $result) {
            $items = $result->getItems();

            if (! $showAll && ($result->getStatus() === DiagnosticResult::STATUS_OK)) {
                continue;
            }

            if (count($items) === 1) {
                $output->writeln($result->getLabel() . ': ' . $this->formatItem($items[0]));
                continue;
            }

            $output->writeln($result->getLabel() . ':');
            foreach ($items as $item) {
                $output->writeln("\t- " . $this->formatItem($item));
            }
        }

        if ($report->hasWarnings()) {
            $output->writeln(sprintf('<comment>%d warnings detected</comment>', $report->getWarningCount()));
        }
        if ($report->hasErrors()) {
            $output->writeln(sprintf('<error>%d errors detected</error>', $report->getErrorCount()));
            return self::FAILURE;
        }

        if (!$report->hasWarnings() && !$report->hasErrors()) {
            $output->writeln(sprintf('<info>%s</info>', Piwik::translate('Installation_SystemCheckSummaryNoProblems')));
        }

        return self::SUCCESS;
    }

    private function formatItem(DiagnosticResultItem $item)
    {
        if ($item->getStatus() === DiagnosticResult::STATUS_ERROR) {
            $tag = 'error';
        } elseif ($item->getStatus() === DiagnosticResult::STATUS_WARNING) {
            $tag = 'comment';
        } else {
            $tag = 'info';
        }

        return sprintf(
            '<%s>%s %s</%s>',
            $tag,
            strtoupper($item->getStatus()),
            preg_replace('%</?[a-z][a-z0-9]*[^<>]*>%sim', '', preg_replace('/\<br\s*\/?\>/i', "\n", $item->getComment())),
            $tag
        );
    }
}
