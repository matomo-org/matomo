<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Filesystem;
use Piwik\Tests\Fixtures\LatestStableInstall;
use Piwik\Tests\Framework\Fixture;

class ComposerInstall extends Fixture
{
    /**
     * @var string
     */
    private $subdirToInstall;

    /**
     * @var string
     */
    private $currentBranch;

    public function __construct($subdirToInstall = 'composerInstall')
    {
        $this->subdirToInstall = $subdirToInstall;
    }

    public function setUp(): void
    {
        $this->currentBranch = $this->getCurrentBranch();
        if (empty($this->currentBranch)) {
            throw new \Exception("no branch found, cannot test composer install");
        }

        $this->removeExistingComposerInstall();
        $this->installFromComposer();
        $tokenAuth = LatestStableInstall::installSubdirectoryInstall($this->subdirToInstall . '/vendor/matomo/matomo');
        LatestStableInstall::verifyInstall($tokenAuth, $this->subdirToInstall);
    }

    private function removeExistingComposerInstall()
    {
        $installSubdirectory = $this->getInstallSubdirectoryPath();
        if (is_dir($installSubdirectory)) {
            Filesystem::unlinkRecursive($installSubdirectory, true);
        }

        Filesystem::mkdir($installSubdirectory);
    }

    private function getInstallSubdirectoryPath()
    {
        return PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . $this->subdirToInstall;
    }

    private function installFromComposer()
    {
        $installPath = $this->getInstallSubdirectoryPath();

        // create composer.json
        $composerContents = [
            'name' => 'matomo/test-install',
            'type' => 'project',
            'require' => [
                'matomo/matomo' => 'dev-' . $this->currentBranch,
            ],
            'minimum-stability' => 'dev',
            'prefer-stable' => true,
        ];

        $composerContents = json_encode($composerContents);

        $composerFile = $installPath . '/composer.json';
        file_put_contents($composerFile, $composerContents);

        // run composer install
        $composerCommand = 'cd ' . $installPath . ' && ' . $this->getComposer() . ' install';
        passthru($composerCommand);

        // create root php file proxies
        foreach (['index.php', 'matomo.php', 'console'] as $file) {
            file_put_contents($installPath . '/' . $file, '<?php require_once(__DIR__ . \'/vendor/matomo/matomo/' . $file . '\');');
        }
    }

    private function getCurrentBranch()
    {
        return `git branch --show-current`;
    }

    private function getComposer()
    {
        return getenv('COMPOSER_PATH') ?: 'composer';
    }
}