<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Log\LoggerInterface;

/**
 * Downloads the UI tests screenshots from artifacts server into the local repository.
 *
 * This command helps to synchronize the screenshots after they have changed.
 */
class SyncScreenshots extends ConsoleCommand
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    const BUILDURL = "https://builds-artifacts.matomo.org";

    public function __construct()
    {
        $this->logger = StaticContainer::get(LoggerInterface::class);

        parent::__construct();
    }

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('tests:sync-ui-screenshots');
        $this->setAliases(['development:sync-ui-test-screenshots']);
        $this->setDescription(
            'For Piwik core devs. Copies screenshots from github artifacts to the tests/UI/expected-screenshots/ folder'
        );
        $this->addRequiredArgument(
            'buildnumber',
            'Travis build number you want to sync.'
        );
        $this->addOptionalArgument(
            'screenshotsRegex',
            'A regex to use when selecting screenshots to copy. If not supplied all screenshots are copied.',
            ['.*'],
            true
        );
        $this->addOptionalValueOption(
            'repository',
            'r',
            'Repository name you want to sync screenshots for.',
            'matomo-org/matomo'
        );
        $this->addOptionalValueOption(
            'http-user',
            '',
            'the HTTP AUTH username (for premium plugins where artifacts are protected)'
        );
        $this->addOptionalValueOption(
            'http-password',
            '',
            'the HTTP AUTH password (for premium plugins where artifacts are protected)'
        );
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $buildNumber      = $input->getArgument('buildnumber');
        $screenshotsRegex = $input->getArgument('screenshotsRegex');
        $repository       = $input->getOption('repository');
        $httpUser         = $input->getOption('http-user');
        $httpPassword     = $input->getOption('http-password');

        $screenshots = $this->getScreenshotList($repository, $buildNumber, $httpUser, $httpPassword);

        $this->logger->notice('Downloading {number} screenshots', ['number' => count($screenshots)]);
        foreach ($screenshots as $name => $url) {
            if (empty($name)) {
                continue;
            }

            if (is_array($screenshotsRegex)) {
                foreach ($screenshotsRegex as $regex) {
                    if (preg_match('/' . $regex . '/', $name)) {
                        $this->logger->info('Downloading {name}', ['name' => $name]);
                        $this->downloadScreenshot($url, $repository, $name, $httpUser, $httpPassword);
                        break;
                    }
                }
            } elseif (preg_match('/' . $screenshotsRegex . '/', $name)) {
                $this->logger->info('Downloading {name}', ['name' => $name]);
                $this->downloadScreenshot($url, $repository, $name, $httpUser, $httpPassword);
            }
        }

        $this->displayGitInstructions($repository);

        return self::SUCCESS;
    }

    private function getScreenshotList($repository, $buildNumber, $httpUser = null, $httpPassword = null)
    {
        $url = sprintf('%s/api/%s/%s', self::BUILDURL, $repository, $buildNumber);

        $this->logger->debug('Fetching {url}', ['url' => $url]);

        $response   = Http::sendHttpRequest(
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
        $this->logger->debug('Response content: {content}', ['content' => $response['data']]);
        throw new \Exception("Failed downloading diffviewer from $url - Got HTTP status $httpStatus");
    }

    private function downloadScreenshot($url, $repository, $screenshot, $httpUser, $httpPassword)
    {
        $downloadTo = $this->getDownloadToPath($repository, $screenshot) . $screenshot;

        $url = self::BUILDURL . $url;

        $this->logger->debug("Downloading {url} to {destination}", ['url' => $url, 'destination' => $downloadTo]);

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

    private function displayGitInstructions($repository)
    {
        $this->getOutput()->writeln(
            '<comment>If all downloaded screenshots are valid you may push them with these commands:</comment>'
        );
        $downloadToPath = $this->getDownloadToPath($repository);
        $commands       = "

# Starts here
cd $downloadToPath
git pull
git add .
git status
git commit -m 'UI tests: ...' # Write a good commit message, eg. 'Fixed UI test failure caused by change introduced in X which caused failure by Y'
echo -e \"\n--> Check the commit above is correct... <---\n\"
sleep 7
git push";

        if ($repository === 'matomo-org/matomo') {
            $commands .= "
cd ../../../";
        } else {
            $commands .= "
cd ../../../../../";
        }

        $this->getOutput()->writeln($commands);
    }

    private function getDownloadToPath($repository, $fileName = false)
    {
        $plugin = $this->getPluginName($repository, $fileName);

        if (empty($plugin)) {
            return PIWIK_DOCUMENT_ROOT . "/tests/UI/expected-screenshots/";
        }

        $possibleSubDirs = [
            'expected-screenshots',
            'expected-ui-screenshots',
        ];

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

    private function getPluginName($repository, $fileName)
    {
        [$org, $repository] = explode('/', $repository, 2);

        if (strpos($repository, 'plugin-') === 0) {
            return substr($repository, strlen('plugin-'));
        }

        // determine plugin based on the test name
        if (!empty($fileName)) {
            [$testName, $_null] = explode('_', $fileName, 2);
            $foundExistingFiles = Filesystem::globr(PIWIK_DOCUMENT_ROOT, $fileName);
            $foundTestSpecs     = Filesystem::globr(PIWIK_DOCUMENT_ROOT, $testName . '_spec.js');
            $filesToCheck       = $foundExistingFiles + $foundTestSpecs;

            foreach ($filesToCheck as $file) {
                if (preg_match('/plugins\/([^\/]+)\//i', $file, $plugin)) {
                    return $plugin[1];
                }
            }
        }

        return null;
    }
}
