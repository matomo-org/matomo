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
        $this->verifyInstall($tokenAuth);
    }

    private function removeLatestStableInstall()
    {
        $installSubdirectory = $this->getInstallSubdirectoryPath();
        Filesystem::mkdir($installSubdirectory);

        if (file_exists($installSubdirectory)) {
            Filesystem::unlinkRecursive($installSubdirectory, true);
        }

        $latestStableZip = $this->getArchiveDestPath();
        if (file_exists($latestStableZip)) {
            unlink($latestStableZip);
        }
    }

    private function downloadAndUnzipLatestStable()
    {
        $latestStableChannel = new LatestStable();
        $url = 'http' . $latestStableChannel->getDownloadUrlWithoutScheme(null);

        $archiveFile = $this->getArchiveDestPath();
        Http::fetchRemoteFile($url, $archiveFile, 0, self::DOWNLOAD_TIMEOUT);

        $installSubdirectory = $this->getInstallSubdirectoryPath();
        Filesystem::mkdir($installSubdirectory);

        $archive = Unzip::factory('PclZip', $archiveFile);
        $archiveFiles = $archive->extract($installSubdirectory);

        if (0 == $archiveFiles
            || 0 == count($archiveFiles)
        ) {
            throw new \Exception("Failed to extract matomo build ZIP archive.");
        }

        shell_exec('mv "' . $installSubdirectory . '"/piwik/* "' . $installSubdirectory . '"');

        /**
         * The additional permissions check was added within Matomo 4.8 development. Therefor the OneClickUpdate UI tests
         * would not already perform this check, as it uses the latest stable version to perform an update the the git checkout.
         * As soon as 4.8 has been release, which should include the permission check, this won't be needed anymore.
         *
         * @todo remove this after Matomo 4.8 has been released
         */
        shell_exec('curl https://raw.githubusercontent.com/matomo-org/matomo/updatetest/plugins/CoreUpdater/Updater.php > ' . $installSubdirectory . '/plugins/CoreUpdater/Updater.php');
        shell_exec('curl https://raw.githubusercontent.com/matomo-org/matomo/updatetest/plugins/CoreUpdater/lang/en.json > ' . $installSubdirectory . '/plugins/CoreUpdater/lang/en.json');
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
        $this->runMatomoPackage();
    }

    private function cloneMatomoPackageRepo()
    {
        $pathToMatomoPackage = PIWIK_INCLUDE_PATH . '/../matomo-package';
        if (file_exists($pathToMatomoPackage)) {
            Filesystem::unlinkRecursive($pathToMatomoPackage, true);
        }

        $command = 'git clone https://github.com/matomo-org/matomo-package.git --depth=1 "' . $pathToMatomoPackage . '"';
        exec($command, $output, $returnCode);

        if ($returnCode != 0) {
            throw new \Exception("Could not clone matomo-package repo: " . implode("\n", $output));
        }
    }

    private function runMatomoPackage()
    {
        $matomoBuildPath = PIWIK_INCLUDE_PATH . '/matomo-build.zip';
        if (file_exists($matomoBuildPath)) {
            unlink($matomoBuildPath);
        }

        $command = 'cd "' . PIWIK_INCLUDE_PATH . '/../matomo-package" && ';
        $command .= './scripts/build-package.sh "' . PIWIK_INCLUDE_PATH . '" piwik true';

        exec($command, $output, $returnCode);
        if ($returnCode != 0) {
            throw new \Exception("matomo-package failed: " . implode("\n", $output));
        }

        $path = PIWIK_INCLUDE_PATH . '/../matomo-package/piwik-build.zip';
        rename($path, $matomoBuildPath);
    }
}