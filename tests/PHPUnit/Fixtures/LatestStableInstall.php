<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Config;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugins\CoreUpdater\ReleaseChannel\LatestStable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Unzip;
use Piwik\Version;

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

    public function setUp(): void
    {
        $this->removeLatestStableInstall();

        // create new package from git contents
        $this->generateMatomoPackageFromGit();

        // install latest stable
        $this->downloadAndUnzipLatestStable();
        $tokenAuth = $this->installSubdirectoryInstall();
        $this->placeAndActivateIncompatibleExamplePlugin();
        $this->verifyInstall($tokenAuth);
    }

    public function tearDown(): void
    {
        $this->removeLatestStableInstall();
    }

    private function removeLatestStableInstall()
    {
        $installSubdirectory = $this->getInstallSubdirectoryPath();
        Filesystem::mkdir($installSubdirectory);

        if (file_exists($installSubdirectory)) {
            Filesystem::unlinkRecursive($installSubdirectory, true);
        }

        if (file_exists($this->getBuildArchivePath())) {
            Filesystem::unlinkRecursive($this->getBuildArchivePath(), true);
        }

        $latestStableZip = $this->getArchiveDestPath();
        if (file_exists($latestStableZip)) {
            unlink($latestStableZip);
        }
    }

    protected function downloadAndUnzipLatestStable()
    {
        $url = $this->getDownloadUrl();

        $archiveFile = $this->getArchiveDestPath();
        Http::fetchRemoteFile($url, $archiveFile, 0, self::DOWNLOAD_TIMEOUT);

        $installSubdirectory = $this->getInstallSubdirectoryPath();
        Filesystem::mkdir($installSubdirectory);

        $archive = Unzip::factory('PclZip', $archiveFile);
        $archiveFiles = $archive->extract($installSubdirectory);

        if (
            0 == $archiveFiles
            || 0 == count($archiveFiles)
        ) {
            throw new \Exception("Failed to extract matomo build ZIP archive.");
        }

        shell_exec('mv "' . $installSubdirectory . '"/matomo/* "' . $installSubdirectory . '"');
    }

    protected function getDownloadUrl()
    {
        $latestStableChannel = new LatestStable();
        return 'http' . $latestStableChannel->getDownloadUrlWithoutScheme(null);
    }

    private function installSubdirectoryInstall()
    {
        $installScript = PIWIK_INCLUDE_PATH . '/tests/resources/install-matomo.php';

        $host = parse_url(Fixture::getRootUrl(), PHP_URL_HOST);
        $port = parse_url(Fixture::getRootUrl(), PHP_URL_PORT);
        if (!empty($port)) {
            $host .= ':' . $port;
        }

        $command = "php " . $installScript . " " . $this->subdirToInstall . ' "' . addslashes($this->getDbConfigJson()) . '" ' . $host;

        $output = shell_exec($command);
        $lines = explode("\n", $output);
        $tokenAuth = trim(end($lines));
        if (strlen($tokenAuth) != 32) {
            throw new \Exception("Failed to install new matomo, output: $output");
        }

        return $tokenAuth;
    }

    private function placeAndActivateIncompatibleExamplePlugin()
    {
        $source = PIWIK_DOCUMENT_ROOT . '/plugins/ExampleTracker/';
        $target = $this->getInstallSubdirectoryPath() . '/plugins/ExampleTracker/';
        Filesystem::mkdir($target);
        Filesystem::copyRecursive($source, $target);
        // remove columns to avoid adding them to the database
        Filesystem::unlinkRecursive($target . '/Columns/', true);

        $pluginJson = json_decode(file_get_contents($target . 'plugin.json'), true);
        // mark plugin as incompatible with version we will be updating to
        $pluginJson['require']['matomo'] = '>=4.0.0-b1,<' . Version::VERSION;
        file_put_contents($target . 'plugin.json', json_encode($pluginJson));

        // activate ExampleTracker, having it incompatible to next version
        // deactivating the plugin during update will cause CustomJsTracker plugin to update the tracker file
        chmod($this->getInstallSubdirectoryPath() . '/console', 0775);
        passthru($this->getInstallSubdirectoryPath() . '/console plugin:activate ExampleTracker');
        passthru($this->getInstallSubdirectoryPath() . '/console core:version');
        passthru($this->getInstallSubdirectoryPath() . '/console plugin:list');
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

    private function getBuildArchivePath()
    {
        return PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'archives';
    }

    private function getDbConfigJson()
    {
        $dbConfig = Config::getInstance()->database;
        $dbConfig = json_encode($dbConfig);
        return $dbConfig;
    }

    private function generateMatomoPackageFromGit()
    {
        $matomoBuildPath = PIWIK_INCLUDE_PATH . '/matomo-build.zip';
        if (file_exists($matomoBuildPath)) {
            unlink($matomoBuildPath);
        }

        $command = 'cd ' . PIWIK_INCLUDE_PATH . ' && ';
        $command .= 'chmod 755 ./.github/scripts/*.sh && ';
        $command .= './.github/scripts/build-package.sh build matomo';

        exec($command, $output, $returnCode);
        echo implode("\n", $output);
        if ($returnCode != 0) {
            throw new \Exception("matomo-package failed: " . implode("\n", $output));
        }

        $path = $this->getBuildArchivePath() . '/matomo-build.zip';
        rename($path, $matomoBuildPath);
    }
}
