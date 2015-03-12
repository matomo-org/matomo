<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Development;
use Piwik\Http;
use Piwik\Log;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Tool for core developers to help making UI screenshot builds green.
 *
 */
class DevelopmentSyncUITestScreenshots extends ConsoleCommand
{
    protected $urlBase;

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('development:sync-ui-test-screenshots');
        $this->setDescription('For Piwik core devs. Copies screenshots '
                            . 'from travis artifacts to the tests/UI/expected-ui-screenshots/ folder');
        $this->addArgument('buildnumber', InputArgument::REQUIRED, 'Travis build number you want to sync.');
        $this->addArgument('screenshotsRegex', InputArgument::OPTIONAL,
            'A regex to use when selecting screenshots to copy. If not supplied all screenshots are copied.', '.*');
        $this->addOption('plugin', 'p', InputOption::VALUE_OPTIONAL, 'Plugin name you want to sync screenshots for.');
        $this->addOption('http-user', '', InputOption::VALUE_OPTIONAL, 'the HTTP AUTH username (for premium plugins where artifacts are protected)');
        $this->addOption('http-password', '', InputOption::VALUE_OPTIONAL, 'the HTTP AUTH password (for premium plugins where artifacts are protected)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $screenshotsRegex = $input->getArgument('screenshotsRegex');

        $diffviewer = $this->getDiffviewerContent($input);

        if(empty($diffviewer)) {
            throw new \Exception("Screenshot tests artifacts were not found for this build.");
        }

        $dom = new \DOMDocument();
        $dom->loadHTML($diffviewer);
        foreach ($dom->getElementsByTagName("tr") as $row) {
            $columns = $row->getElementsByTagName("td");

            $processedColumn = $columns->item(3);

            $file = null;
            if ($processedColumn
                && preg_match("/href=\".*\/(.*)\"/", $dom->saveXml($processedColumn), $matches)
            ) {
                $file = $matches[1];
            }

            if ($file !== null
                && preg_match("/" . $screenshotsRegex . "/", $file)
            ) {
                $this->downloadProcessedScreenshot($input, $output, $file);
            }
        }

        $this->displayGitInstructions($input, $output);

    }

    protected function displayGitInstructions(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('--------------');
        $output->writeln('');
        $output->writeln("If all downloaded screenshots are valid you may push them with these commands:");
        $downloadToPath = $this->getDownloadToPath($input);
        $commands = "
cd $downloadToPath
git pull
git add .
git commit -m '' # Write a good commit message
git push";

        $plugin = $input->getOption('plugin');
        if(empty($plugin)) {
            $commands .= "
cd ..
git pull
git add expected-ui-screenshots/
git commit -m '' # Copy paste the good commit message
git push
cd ../../\n\n";
        } else {
            $commands .= "
cd ../../../../../\n\n";
        }
        $output->writeln($commands);
    }

    protected function getUrlBase(InputInterface $input)
    {
        $buildNumber = $input->getArgument('buildnumber');
        if (empty($buildNumber)) {
            throw new \InvalidArgumentException('Missing build number.');
        }

        $plugin = $input->getOption('plugin');
        if ($plugin) {
            $urlBase = sprintf('http://builds-artifacts.piwik.org/ui-tests.master.%s/%s', $plugin, $buildNumber);
        } else {
            $urlBase = sprintf('http://builds-artifacts.piwik.org/ui-tests.master/%s', $buildNumber);
        }
        return $urlBase;
    }

    protected function getDownloadToPath(InputInterface $input)
    {
        $plugin = $input->getOption('plugin');

        $downloadTo = PIWIK_DOCUMENT_ROOT . "/";
        if (empty($plugin)) {
            $downloadTo .= "tests/UI/expected-ui-screenshots/";
        } else {
            $downloadTo .= "plugins/$plugin/tests/UI/expected-ui-screenshots/";
        }
        if(is_dir($downloadTo)) {
            return $downloadTo;
        }
        $downloadTo = str_replace("tests/", "Test/", $downloadTo);
        if(is_dir($downloadTo)) {
            return $downloadTo;
        }

        throw new \Exception("Download to path could not be found: $downloadTo");
    }

    protected function getDiffviewerContent(InputInterface $input)
    {
        $this->urlBase = $this->getUrlBase($input);
        $diffviewerUrl = $this->getDiffviewerUrl($this->urlBase);

        $diffviewer = $this->downloadDiffviewer($diffviewerUrl);
        if($diffviewer) {
            return $diffviewer;
        }

        // Maybe this is a Premium Piwik PRO plugin...
        return $this->getDiffviewContentForPrivatePlugin($input);
    }

    protected function getDiffviewContentForPrivatePlugin(InputInterface $input)
    {
        $httpUser = $input->getOption('http-user');
        $httpPassword = $input->getOption('http-password');
        if (empty($httpUser) || empty($httpPassword)) {
            Log::info("--http-user and --http-password was not specified, skip download of private plugins screenshots.");
            return;
        }

        // Attempt to download from protected/ artifacts...
        $this->urlBase = str_replace("builds-artifacts.piwik.org/", "builds-artifacts.piwik.org/protected/", $this->urlBase);
        $diffviewerUrl = $this->getDiffviewerUrl($this->urlBase);

        return $this->downloadDiffviewer($diffviewerUrl, $httpUser, $httpPassword);
    }

    /**
     * @return string
     */
    protected function getDiffviewerUrl($urlBase)
    {
        return $urlBase . "/screenshot-diffs/diffviewer.html";
    }

    protected function downloadDiffviewer($urlDiffviewer, $httpUsername = false, $httpPassword = false)
    {
        $responseExtended = Http::sendHttpRequest(
            $urlDiffviewer,
            $timeout = 60,
            $userAgent = null,
            $destinationPath = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $httpUsername,
            $httpPassword
        );
        $httpStatus = $responseExtended['status'];
        if ($httpStatus == '200') {
            $diffviewer = str_replace('&', '&amp;', $responseExtended['data']);
            return $diffviewer;
        }

        Log::info("Could not download the diffviewer from $urlDiffviewer - Got HTTP status " . $httpStatus);
        if($httpStatus == '401') {
            Log::warning("HTTP AUTH username and password are not valid.");
        }
        return false;
    }


    protected function downloadProcessedScreenshot(InputInterface $input, OutputInterface $output, $file)
    {
        $downloadTo = $this->getDownloadToPath($input) . $file;

        $output->write("<info>Downloading $file to  $downloadTo...</info>\n");
        $urlProcessedScreenshot = $this->urlBase . "/processed-ui-screenshots/$file";

        Http::sendHttpRequest($urlProcessedScreenshot,
            $timeout = 60,
            $userAgent = null,
            $downloadTo,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $input->getOption('http-user'),
            $input->getOption('http-password'));
    }

}
