<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugins\CoreUpdater\ReleaseChannel\LatestStable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Unzip;
use Piwik\UpdateCheck\ReleaseChannel;
use Piwik\Url;
use Piwik\Plugins\CoreUpdater;

class GitCommitReleaseChannel extends ReleaseChannel
{
    public function getId()
    {
        return 'git_commit';
    }

    public function getName()
    {
        return 'Test Release Channel';
    }

    public function getUrlToCheckForLatestAvailableVersion()
    {
        return Fixture::getRootUrl() . '/tests/resources/one-click-update-version.php';
    }

    public function getDownloadUrlWithoutScheme($version)
    {
        return '://' . Url::getHost(false) . '/matomo-package/matomo-build.zip';
    }
}

class LatestStableInstall extends Fixture
{
    const DOWNLOAD_TIMEOUT = 900;
    /**
     * @var string
     */
    private $subdirToInstall;

    public function __construct($subdirToInstall = 'latestStableInstall')
    {
        $this->subdirToInstall = $subdirToInstall;
    }

    public function setUp()
    {
        // create new package from git contents
        $this->generateMatomoPackageFromGit();
        $this->setTestReleaseChannel();

        // install latest stable
        $this->downloadAndUnzipLatestStable();
        $tokenAuth = $this->installSubdirectoryInstall();
        $this->verifyInstall($tokenAuth);
    }

    private function downloadAndUnzipLatestStable()
    {
        $latestStableChannel = new LatestStable();
        $url = 'http' . $latestStableChannel->getDownloadUrlWithoutScheme(null);

        $archiveFile = $this->getArchiveDestPath();
        Http::fetchRemoteFile($url, $archiveFile, 0, self::DOWNLOAD_TIMEOUT);

        $installSubdirectory = $this->getInstallSubdirectoryPath();
        Filesystem::mkdir($installSubdirectory);

        if (file_exists($installSubdirectory)) {
            Filesystem::unlinkRecursive($installSubdirectory, true);
        }

        $archive = Unzip::factory('PclZip', $archiveFile);
        $archiveFiles = $archive->extract($installSubdirectory);

        if (0 == $archiveFiles
            || 0 == count($archiveFiles)
        ) {
            throw new \Exception("Failed to extract matomo build ZIP archive.");
        }

        shell_exec('mv "' . $installSubdirectory . '"/piwik/* "' . $installSubdirectory . '"');
    }

    private function installSubdirectoryInstall()
    {
        $installScript = PIWIK_INCLUDE_PATH . '/tests/resources/install-matomo.php';
        $command = "php " . $installScript . " " . $this->subdirToInstall . ' "' . escapeshellarg($this->getDbConfigJson()) . '" '
            . Url::getHost(false);
        print $command . "\n";
        $output = shell_exec($command);
        $lines = explode("\n", $output);
        $tokenAuth = trim(end($lines));
        if (strlen($tokenAuth) != 32) {
            throw new \Exception("Failed to install new matomo, output: $output");
        }

        return $tokenAuth;
    }

    private function verifyInstall($tokenAuth)
    {
        $url = Fixture::getRootUrl() . '/' . $this->subdirToInstall
            . '/index.php?module=API&method=API.get&idSite=1&date=yesterday&period=day&format=json&token_auth=' . $tokenAuth;
        $response = Http::sendHttpRequest($url, 30);

        $response = json_decode($response, true);
        $this->assertEquals(0, $response['nb_visits']);
    }

    private function getArchiveDestPath()
    {
        return PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'test_latest_stable.zip';
    }

    private function getInstallSubdirectoryPath()
    {
        return PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . $this->subdirToInstall;
    }

    private function getDbConfigJson()
    {
        $dbConfig = Config::getInstance()->database;
        $dbConfig = json_encode($dbConfig);
        return $dbConfig;
    }

    private function generateMatomoPackageFromGit()
    {
        $this->cloneMatomoPackageRepo();
        $gitCommit = $this->getGitCommit();
        $this->runMatomoPackage($gitCommit);
    }

    private function cloneMatomoPackageRepo()
    {
        $command = 'git clone git@github.com:matomo-org/matomo-package.git --branch=one-click-test --depth=1 "' . PIWIK_INCLUDE_PATH . '/../matomo-package"';
        exec($command, $output, $returnCode);

        if ($returnCode != 0) {
            throw new \Exception("Could not clone matomo-package repo: " . implode("\n", $output));
        }
    }

    private function getGitCommit()
    {
        $result = `git rev-parse --short HEAD`;
        if (strlen($result) > 32) {
            throw new \Exception('Failed to get current short git commit: ' . $result);
        }
        return $result;
    }

    private function runMatomoPackage($gitCommit)
    {
        $command = 'cd "' . PIWIK_INCLUDE_PATH . '/../matomo-package" && ';
        $command .= './scripts/build-package.sh ' . $gitCommit . ' matomo false true';

        exec($command, $output, $returnCode);
        if ($returnCode != 0) {
            throw new \Exception("matomo-package failed: " . implode("\n", $output));
        }

        $path = PIWIK_INCLUDE_PATH . '/../matomo-package/matomo-' . $gitCommit . '.zip';
        rename($path, 'matomo-build.zip');
    }

    private function setTestReleaseChannel()
    {
        $settings = StaticContainer::get(CoreUpdater\SystemSettings::class);
        $settings->releaseChannel->setValue('git_commit');
        $settings->releaseChannel->save();
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => [
                ['ReleaseChannels.getAllReleaseChannels', function (&$channels) {
                    $channels[] = new GitCommitReleaseChannel();
                }],
            ],
        ];
    }
}