<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Downloads the UI tests screenshots from Travis into the local repository.
 *
 * This command helps synchronizing the screenshots after they have changed.
 */
class SyncScreenshots extends ConsoleCommand
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->logger = StaticContainer::get('Psr\Log\LoggerInterface');

        parent::__construct();
    }

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('tests:sync-ui-screenshots');
        $this->setAliases(array('development:sync-ui-test-screenshots'));
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
        $buildNumber = $input->getArgument('buildnumber');
        if (empty($buildNumber)) {
            throw new \InvalidArgumentException('Missing build number.');
        }

        $screenshotsRegex = $input->getArgument('screenshotsRegex');

        $plugin = $input->getOption('plugin');

        $httpUser = $input->getOption('http-user');
        $httpPassword = $input->getOption('http-password');

        $urlBase = $this->getUrlBase($plugin, $buildNumber);
        $diffviewer = $this->getDiffviewerContent($output, $urlBase, $httpUser, $httpPassword);

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
                $this->downloadProcessedScreenshot($output, $urlBase, $file, $plugin, $httpUser, $httpPassword);
            }
        }

        $this->displayGitInstructions($output, $plugin);

    }

    private function displayGitInstructions(OutputInterface $output, $plugin)
    {
        $output->writeln('');
        $output->writeln("If all downloaded screenshots are valid you may push them with these commands:");
        $downloadToPath = $this->getDownloadToPath($plugin);
        $commands = "
cd $downloadToPath
git pull
git add .
git status
sleep 5
git commit -m '' # Write a good commit message, eg. 'Fixed UI test failure caused by change introduced in <core or plugin commit> which caused failure by <explanation of failure>'
git push";

        if(empty($plugin)) {
            $commands .= "
cd ..
git pull
git add expected-ui-screenshots/
git status
git commit -m '' # Copy paste the good commit message
git push
cd ../../";
        } else {
            $commands .= "
cd ../../../../../";
        }
        $output->writeln($commands);
    }

    private function getUrlBase($plugin, $buildNumber)
    {
        if ($plugin) {
            return sprintf('http://builds-artifacts.piwik.org/ui-tests.master.%s/%s', $plugin, $buildNumber);
        }
        return sprintf('http://builds-artifacts.piwik.org/ui-tests.master/%s', $buildNumber);
    }

    private function getDownloadToPath($plugin)
    {
        if (empty($plugin)) {
            return PIWIK_DOCUMENT_ROOT . "/tests/UI/expected-ui-screenshots/";
        }

        $downloadTo = PIWIK_DOCUMENT_ROOT . "/plugins/$plugin/tests/UI/expected-ui-screenshots/";
        if(is_dir($downloadTo)) {
            return $downloadTo;
        }

        // Maybe the plugin is using folder "Test/" instead of "tests/"
        $downloadTo = str_replace("tests/", "Test/", $downloadTo);
        if(is_dir($downloadTo)) {
            return $downloadTo;
        }
        throw new \Exception("Download to path could not be found: $downloadTo");
    }

    private function getDiffviewerContent(OutputInterface $output, &$urlBase, $httpUser = false, $httpPassword = false)
    {
        $url = $this->getDiffviewerUrl($urlBase);

        try {
            $diffViewer = $this->downloadDiffviewer($output, $url);
        } catch (\Exception $e) {
            $this->logger->debug('Not found: {url}', array('url' => $url));

            // Maybe this is a Premium Piwik PRO plugin...
            $diffViewer = $this->getDiffviewContentForPrivatePlugin($output, $urlBase, $httpUser, $httpPassword);
        }

        if (empty($diffViewer)) {
            throw new \Exception("Screenshot tests artifacts were not found for this build.");
        }
        return $diffViewer;
    }

    private function getDiffviewContentForPrivatePlugin(OutputInterface $output, &$urlBase, $httpUser, $httpPassword)
    {
        if (empty($httpUser) || empty($httpPassword)) {
            $output->writeln("<info>--http-user and --http-password was not specified, skip download of private plugins screenshots.</info>");
            return;
        }

        // Attempt to download from protected/ artifacts...
        $urlBase = str_replace("builds-artifacts.piwik.org/", "builds-artifacts.piwik.org/protected/", $urlBase);
        $diffviewerUrl = $this->getDiffviewerUrl($urlBase);

        return $this->downloadDiffviewer($output, $diffviewerUrl, $httpUser, $httpPassword);
    }

    /**
     * @return string
     */
    private function getDiffviewerUrl($urlBase)
    {
        return $urlBase . "/screenshot-diffs/diffviewer.html";
    }

    private function downloadDiffviewer(OutputInterface $output, $urlDiffviewer, $httpUsername = false, $httpPassword = false)
    {
        $this->logger->debug('Downloading {url}', array('url' => $urlDiffviewer));

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
            $output->writeln("Found diffviewer at: " . $urlDiffviewer);
            $diffviewer = str_replace('&', '&amp;', $responseExtended['data']);
            return $diffviewer;
        }

        if($httpStatus == '401') {
            $output->writeln("<error>HTTP AUTH username and password are not valid.</error>");
        }
        throw new \Exception ("Failed downloading diffviewer from $urlDiffviewer - Got HTTP status " . $httpStatus);
    }


    private function downloadProcessedScreenshot(OutputInterface $output, $urlBase, $file, $plugin, $httpUser, $httpPassword)
    {
        $downloadTo = $this->getDownloadToPath($plugin) . $file;

        $output->write("<info>Downloading $file to  $downloadTo...</info>\n");
        $screenshotUrl = $urlBase . "/processed-ui-screenshots/$file";

        $this->logger->debug('Downloading {url}', array('url' => $screenshotUrl));

        Http::sendHttpRequest(
            $screenshotUrl,
            $timeout = 60,
            $userAgent = null,
            $downloadTo,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $httpUser,
            $httpPassword
        );
    }

}
