<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class DevelopmentSyncUITestScreenshots extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('development:sync-ui-test-screenshots');
        $this->setDescription('For Piwik core devs. Copies screenshots '
                            . 'from travis artifacts to tests/PHPUnit/UI/expected-ui-screenshots/');
        $this->addArgument('buildnumber', InputArgument::REQUIRED, 'Travis build number you want to sync.');
        $this->addArgument('screenshotsRegex', InputArgument::OPTIONAL,
            'A regex to use when selecting screenshots to copy. If not supplied all screenshots are copied.', '.*');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildNumber = $input->getArgument('buildnumber');
        $screenshotsRegex = $input->getArgument('screenshotsRegex');

        if (empty($buildNumber)) {
            throw new \InvalidArgumentException('Missing build number.');
        }

        $urlBase = sprintf('http://builds-artifacts.piwik.org/ui-tests.master/%s', $buildNumber);
        $diffviewer = Http::sendHttpRequest($urlBase . "/screenshot-diffs/diffviewer.html", $timeout = 60);
        $diffviewer = str_replace('&', '&amp;', $diffviewer);

        $dom = new \DOMDocument();
        $dom->loadHTML($diffviewer);
        foreach ($dom->getElementsByTagName("tr") as $row) {
            $columns = $row->getElementsByTagName("td");

            $nameColumn = $columns->item(0);
            $processedColumn = $columns->item(3);

            $testPlugin = null;
            if ($nameColumn
                && preg_match("/\(for ([a-zA-Z_]+) plugin\)/", $dom->saveXml($nameColumn), $matches)
            ) {
                $testPlugin = $matches[1];
            }

            $file = null;
            if ($processedColumn
                && preg_match("/href=\".*\/(.*)\"/", $dom->saveXml($processedColumn), $matches)
            ) {
                $file = $matches[1];
            }

            if ($file !== null
                && preg_match("/" . $screenshotsRegex . "/", $file)
            ) {
                if ($testPlugin == null) {
                    $downloadTo = "tests/PHPUnit/UI/expected-ui-screenshots/$file";
                } else {
                    $downloadTo = "plugins/$testPlugin/tests/UI/expected-ui-screenshots/$file";
                }

                $output->write("<info>Downloading $file to  $downloadTo...</info>\n");
                Http::sendHttpRequest("$urlBase/processed-ui-screenshots/$file", $timeout = 60, $userAgent = null,
                    PIWIK_DOCUMENT_ROOT . "/" . $downloadTo);
            }
        }

        $this->displayGitInstructions($output);

    }

    /**
     * @param OutputInterface $output
     */
    protected function displayGitInstructions(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('--------------');
        $output->writeln('');
        $output->writeln("If all downloaded screenshots are valid you may push them with these commands:");
        $output->writeln('');
        $commands = "cd tests/PHPUnit/UI/
git add expected-ui-screenshots/
git pull
git commit -m'' # WRITE A COMMIT MESSAGE
git push
cd ..
git add UI
git pull
git commit -m'' #WRITE A COMMIT MESSAGE
git push";
        $output->writeln($commands);
    }
}
