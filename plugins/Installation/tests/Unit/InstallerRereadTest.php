<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation\tests\Unit;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Installation\Controller;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * DB-free coverage of the installer's on-disk re-read guard: the predicate itself, and each
 * guarded method calling the abort before it writes or deletes when a completed config is on disk.
 *
 * The abort ends the request in production; here it is replaced with a throwing partial mock so the
 * guard's effect can be asserted. Because the abort short-circuits ahead of any database work, all
 * of this stays DB-free.
 *
 * @group Installation
 */
class InstallerRereadTest extends TestCase
{
    /** @var Controller */
    private $controller;

    /** @var string */
    private $dir;

    /** @var string */
    private $localPath;

    /** @var Config */
    private $originalConfig;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = (new ReflectionClass(Controller::class))->newInstanceWithoutConstructor();

        $this->dir = sys_get_temp_dir() . '/matomo-installer-reread-' . uniqid('', true);
        mkdir($this->dir, 0777, true);
        $this->localPath = $this->dir . '/config.ini.php';

        $this->originalConfig = StaticContainer::getContainer()->get('Piwik\Config');

        // Point the config singleton at the throwaway path while it is still absent, so the
        // helper's own fresh read (not the provider's eager parse) is what gets exercised.
        $this->pointConfigAtLocalPath();
    }

    public function tearDown(): void
    {
        StaticContainer::getContainer()->set('Piwik\Config', $this->originalConfig);

        foreach ((array) glob($this->dir . '/*') as $file) {
            @unlink($file);
        }
        @rmdir($this->dir);

        parent::tearDown();
    }

    private function pointConfigAtLocalPath(): void
    {
        $config = new Config(new GlobalSettingsProvider(null, $this->localPath));
        StaticContainer::getContainer()->set('Piwik\Config', $config);
    }

    private function looksInstalled(): bool
    {
        $ref = new ReflectionMethod(Controller::class, 'onDiskConfigLooksInstalled');
        $ref->setAccessible(true);

        return $ref->invoke($this->controller);
    }

    public function testLooksInstalledForCompletedConfig(): void
    {
        file_put_contents($this->localPath, "; <?php exit; ?>\n[database]\nusername = \"root\"\n");

        $this->assertTrue($this->looksInstalled());
    }

    public function testDoesNotLookInstalledForPreDatabaseConfig(): void
    {
        // A legitimate fresh installer file carries no database credentials yet.
        file_put_contents($this->localPath, "; <?php exit; ?>\n[General]\ninstallation_first_accessed = 123\n");
        $this->assertFalse($this->looksInstalled());
    }

    public function testDoesNotLookInstalledForInProgressConfig(): void
    {
        // Database present but still flagged as an installation in progress.
        file_put_contents(
            $this->localPath,
            "; <?php exit; ?>\n[database]\nusername = \"root\"\n[General]\ninstallation_in_progress = 1\n"
        );
        $this->assertFalse($this->looksInstalled());
    }

    public function testDoesNotLookInstalledForDatabaseSectionWithoutUsername(): void
    {
        file_put_contents($this->localPath, "; <?php exit; ?>\n[database]\nhost = \"127.0.0.1\"\n");
        $this->assertFalse($this->looksInstalled());
    }

    public function testDoesNotLookInstalledForMissingFile(): void
    {
        // Never block a genuine fresh install: an absent file reads as not installed.
        $this->assertFalse($this->looksInstalled());
    }

    public function testDoesNotLookInstalledForUnparsableFile(): void
    {
        file_put_contents($this->localPath, "; <?php exit; ?>\n[database\nusername = \"root\n");

        $this->assertFalse($this->looksInstalled());
    }

    public function testCreateConfigFileAbortsWhenCompletedConfigOnDisk(): void
    {
        $this->writeCompletedConfig();

        $controller = $this->newAbortingController();

        $this->expectException(\DomainException::class);
        $this->invokeOn(
            $controller,
            'createConfigFile',
            [['username' => 'root', 'password' => '', 'dbname' => 'test', 'host' => '127.0.0.1', 'tables_prefix' => '']]
        );
    }

    public function testDeleteConfigFileAbortsWhenCompletedConfigOnDisk(): void
    {
        $this->writeCompletedConfig();

        $controller = $this->newAbortingController();

        $this->expectException(\DomainException::class);
        $this->invokeOn($controller, 'deleteConfigFileIfNeeded');
    }

    public function testSetUpInstallationExpirationAbortsWhenCompletedConfigOnDisk(): void
    {
        $this->writeCompletedConfig();

        $controller = $this->newAbortingController();

        // No first-access marker in memory, so the early return is skipped and the guard is reached.
        $config = new Config(new GlobalSettingsProvider(null, $this->localPath));

        $this->expectException(\DomainException::class);
        $this->invokeOn($controller, 'setUpInstallationExpiration', [$config, 1700000000]);
    }

    public function testSetUpInstallationExpirationProceedsOnFreshInstall(): void
    {
        // No config file on disk: the guard must not fire and the first-access marker is written.
        $controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['abortAsAlreadyInstalled'])
            ->disableOriginalConstructor()
            ->getMock();
        $controller->expects($this->never())->method('abortAsAlreadyInstalled');

        $config = new Config(new GlobalSettingsProvider(null, $this->localPath));
        $this->invokeOn($controller, 'setUpInstallationExpiration', [$config, 1700000000]);

        $this->assertFileExists($this->localPath);
        $written = new Config(new GlobalSettingsProvider(null, $this->localPath));
        $this->assertEquals(1700000000, $written->General['installation_first_accessed']);
    }

    private function writeCompletedConfig(): void
    {
        file_put_contents($this->localPath, "; <?php exit; ?>\n[database]\nusername = \"root\"\n");
    }

    /**
     * @return Controller
     */
    private function newAbortingController()
    {
        $controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['abortAsAlreadyInstalled'])
            ->disableOriginalConstructor()
            ->getMock();

        // Stand in for the production exit(): halt execution at the guard so it can be asserted.
        $controller->expects($this->once())
            ->method('abortAsAlreadyInstalled')
            ->willThrowException(new \DomainException('aborted'));

        return $controller;
    }

    private function invokeOn($object, string $method, array $args = [])
    {
        $ref = new ReflectionMethod(Controller::class, $method);
        $ref->setAccessible(true);

        return $ref->invoke($object, ...$args);
    }
}
