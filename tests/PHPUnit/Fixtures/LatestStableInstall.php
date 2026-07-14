<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    public const DOWNLOAD_TIMEOUT = 900;

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
        $this->preloadRelocatedClassesBeforeFileSwap();
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

    /**
     * Emulates the fix that needs to ship in the Matomo version we update *from* (5.x).
     *
     * psr/log relocated its class files (Psr/Log -> src) between its major versions. During a one click
     * update the running (pre-update) process replaces the files via Updater::installNewFiles() but keeps
     * its already-initialised composer autoloader, which can then no longer resolve the relocated psr/log
     * classes from their old paths. Rendering the updater result therefore fatals with
     * "Class Psr\Log\NullLogger not found" before the request finishes.
     *
     * Loading those classes before the files are swapped keeps them available in memory for the rest of
     * that request. We patch the downloaded stable install accordingly so the one click update test can
     * verify the 5 -> 6 upgrade path. The actual fix has to be released in a 5.x version.
     */
    private function preloadRelocatedClassesBeforeFileSwap(): void
    {
        $updaterFile = $this->getInstallSubdirectoryPath() . '/plugins/CoreUpdater/Updater.php';

        if (!file_exists($updaterFile)) {
            throw new \Exception("Could not find CoreUpdater Updater.php in the stable install to patch.");
        }

        $contents = file_get_contents($updaterFile);

        // Preload the whole (small) psr/log package: resolving the logger during the update touches
        // several of its classes (e.g. NullLogger and LogLevel), so loading them all keeps them in memory
        // for the rest of the request regardless of which ones the resolution happens to need.
        $preload = <<<'PHP'
foreach (['Psr\Log\LoggerInterface', 'Psr\Log\AbstractLogger', 'Psr\Log\NullLogger', 'Psr\Log\LoggerTrait', 'Psr\Log\LogLevel', 'Psr\Log\InvalidArgumentException', 'Psr\Log\LoggerAwareInterface', 'Psr\Log\LoggerAwareTrait'] as $classToPreload) { class_exists($classToPreload) || interface_exists($classToPreload) || trait_exists($classToPreload); }
PHP;

        $count = 0;
        $patched = preg_replace_callback(
            '/^([ \t]*)(\$this->installNewFiles\(.*?\);)/m',
            function ($matches) use ($preload) {
                return $matches[1] . $preload . "\n" . $matches[1] . $matches[2];
            },
            $contents,
            1,
            $count
        );

        if (1 !== $count) {
            throw new \Exception("Could not patch the stable install Updater.php to preload relocated classes.");
        }

        file_put_contents($updaterFile, $patched);
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

    protected function getInstallSubdirectoryPath()
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
