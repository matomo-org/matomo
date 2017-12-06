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
                            . 'from travis artifacts to the tests/UI/expected-screenshots/ folder');
        $this->addArgument('buildnumber', InputArgument::REQUIRED, 'Travis build number you want to sync.');
        $this->addArgument('screenshotsRegex', InputArgument::OPTIONAL,
            'A regex to use when selecting screenshots to copy. If not supplied all screenshots are copied.', '.*');
        $this->addOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'Repository name you want to sync screenshots for.', 'piwik/piwik');
        $this->addOption('http-user', '', InputOption::VALUE_OPTIONAL, 'the HTTP AUTH username (for premium plugins where artifacts are protected)');
        $this->addOption('http-password', '', InputOption::VALUE_OPTIONAL, 'the HTTP AUTH password (for premium plugins where artifacts are protected)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildNumber = $input->getArgument('buildnumber');
        $screenshotsRegex = $input->getArgument('screenshotsRegex');
        $repository = $input->getOption('repository');
        $httpUser = $input->getOption('http-user');
        $httpPassword = $input->getOption('http-password');

        $screenshots = $this->getScreenshotList($repository, $buildNumber, $httpUser, $httpPassword);

        $this->logger->notice('Downloading {number} screenshots', array('number' => count($screenshots)));
        foreach ($screenshots as $name => $url) {
            if (preg_match('/' . $screenshotsRegex . '/', $name)) {
                $this->logger->info('Downloading {name}', array('name' => $name));
                $this->downloadScreenshot($url, $repository, $name, $httpUser, $httpPassword);
            }
        }

        $this->displayGitInstructions($output, $repository);
    }

    private function getScreenshotList($repository, $buildNumber, $httpUser = null, $httpPassword = null)
    {
        $url = sprintf('https://builds-artifacts.piwik.org/api/%s/%s', $repository, $buildNumber);

        $this->logger->debug('Fetching {url}', array('url' => $url));

        $response = Http::sendHttpRequest(
            $url,
            $timeout = 160,
            $userAgent = null,
            $destinationPath = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $httpUser,
            $httpPassword
        );
        $httpStatus = $response['status'];
        if ($httpStatus == '200') {
            return json_decode($response['data'], true);
        }
        if ($httpStatus == '401') {
            throw new \Exception('HTTP 401 - Auth username and password are invalid');
        }
        $this->logger->debug('Response content: {content}', array('content' => $response['data']));
        throw new \Exception("Failed downloading diffviewer from $url - Got HTTP status $httpStatus");
    }

    private function downloadScreenshot($url, $repository, $screenshot, $httpUser, $httpPassword)
    {
        $downloadTo = $this->getDownloadToPath($repository) . $screenshot;
        $url = 'https://builds-artifacts.piwik.org' . $url;

        $this->logger->debug("Downloading {url} to {destination}", array('url' => $url, 'destination' => $downloadTo));

        Http::sendHttpRequest(
            $url,
            $timeout = 160,
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

    private function displayGitInstructions(OutputInterface $output, $repository)
    {
        $output->writeln('<comment>If all downloaded screenshots are valid you may push them with these commands:</comment>');
        $downloadToPath = $this->getDownloadToPath($repository);
        $commands = "

# Starts here
cd $downloadToPath
git pull
git add .
git status
git commit -m 'UI tests: ...' # Write a good commit message, eg. 'Fixed UI test failure caused by change introduced in X which caused failure by Y'
echo -e \"\n--> Check the commit above is correct... <---\n\"
sleep 7
git push";

        if ($repository === 'piwik/piwik') {
            $commands .= "
cd ../../../";
        } else {
            $commands .= "
cd ../../../../../";
        }

        $output->writeln($commands);
    }

    private function getDownloadToPath($repository)
    {
        $plugin = $this->getPluginName($repository);

        if (empty($plugin)) {
            return PIWIK_DOCUMENT_ROOT . "/tests/UI/expected-screenshots/";
        }

        $possibleSubDirs = array(
            'expected-screenshots',
            'expected-ui-screenshots'
        );

        foreach ($possibleSubDirs as $subDir) {
            $downloadTo = PIWIK_DOCUMENT_ROOT . "/plugins/$plugin/tests/UI/$subDir/";
            if (is_dir($downloadTo)) {
                return $downloadTo;
            }

            // Maybe the plugin is using folder "Test/" instead of "tests/"
            $downloadTo = str_replace("tests/", "Test/", $downloadTo);
            if (is_dir($downloadTo)) {
                return $downloadTo;
            }
        }
        throw new \Exception("Download to path could not be found: $downloadTo");
    }

    private function getPluginName($repository)
    {
        list($org, $repository) = explode('/', $repository, 2);

        if (strpos($repository, 'plugin-') === 0) {
            return substr($repository, strlen('plugin-'));
        }
        return null;
    }
}
