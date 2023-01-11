<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Container\StaticContainer;
use Piwik\FileIntegrity;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Piwik\Plugins\Diagnostics\DiagnosticService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run the diagnostics.
 */
class Run extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:run')
            ->setDescription('Run diagnostics to check that Piwik is installed and runs correctly')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Show all diagnostics, including those that passed with success')
            ->addOption('show-unexpected-files', null, InputOption::VALUE_NONE, 'Show a list of unexpected files found in the Matomo installation directory')
            ->addOption('delete-unexpected-files', null, InputOption::VALUE_NONE, 'Delete any unexpected files found in the Matomo installation directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Replace this with dependency injection once available
        /** @var DiagnosticService $diagnosticService */
        $diagnosticService = StaticContainer::get('Piwik\Plugins\Diagnostics\DiagnosticService');

        $showAll = $input->getOption('all');

        // Output or delete a list of unexpected files and then quit if the option is set
        $showUnexpectedFiles = $input->getOption('show-unexpected-files');
        $deleteUnexpectedFiles = $input->getOption('delete-unexpected-files');
        if ($showUnexpectedFiles || $deleteUnexpectedFiles) {
            return $this->runUnexpectedFiles($deleteUnexpectedFiles);
        }

        $report = $diagnosticService->runDiagnostics();

        foreach ($report->getAllResults() as $result) {
            $items = $result->getItems();

            if (! $showAll && ($result->getStatus() === DiagnosticResult::STATUS_OK)) {
                continue;
            }

            if (count($items) === 1) {
                $output->writeln($result->getLabel() . ': ' . $this->formatItem($items[0]), OutputInterface::OUTPUT_NORMAL);
                continue;
            }

            $output->writeln($result->getLabel() . ':');
            foreach ($items as $item) {
                $output->writeln("\t- " . $this->formatItem($item), OutputInterface::OUTPUT_NORMAL);
            }
        }

        if ($report->hasWarnings()) {
            $output->writeln(sprintf('<comment>%d warnings detected</comment>', $report->getWarningCount()));
        }
        if ($report->hasErrors()) {
            $output->writeln(sprintf('<error>%d errors detected</error>', $report->getErrorCount()));
            return 1;
        }

        if(!$report->hasWarnings() && !$report->hasErrors()) {
            $output->writeln(sprintf('<info>%s</info>', Piwik::translate('Installation_SystemCheckSummaryNoProblems')));
        }

        return 0;
    }

    /**
     * Handle unexpected files command options
     *
     * @param bool $delete
     *
     * @return int
     */
    private function runUnexpectedFiles(bool $delete = false): int
    {
        $files = FileIntegrity::getUnexpectedFilesList();
        $fails = 0;
        foreach ($files as $f) {

            $fileName = realpath($f);

            if ($delete) {
                if (Filesystem::deleteFileIfExists($fileName)) {
                    echo "Deleted unexpected file '".$fileName."'\n";
                } else {
                    echo "Failed to delete unexpected file '".$fileName."'\n";
                    $fails++;
                }
            } else {
                echo $fileName."\n";
            }
        }
        if ($delete && $fails) {
            echo "Failed to delete ".$fails." unexpected files'\n";
            return 1;
        }
        return 0;
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
