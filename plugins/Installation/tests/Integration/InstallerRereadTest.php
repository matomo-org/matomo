<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation\tests\Integration;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Installation\Controller;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * DB-backed coverage of the installer's on-disk re-read guard.
 *
 * Only the flows that pass the guard are driven end to end here. The guard's stop branch ends the
 * request (Piwik::exitWithErrorMessage), which cannot be asserted in-process, so it is not covered
 * here; the predicate it keys on is exercised by the unit test instead.
 *
 * @group Installation
 */
class InstallerRereadTest extends IntegrationTestCase
{
    /** @var Fixture */
    public static $fixture;

    /** @var Controller */
    private $controller;

    /** @var string */
    private $dir;

    /** @var string */
    private $localPath;

    /** @var Config */
    private $originalConfig;

    /** @var GlobalSettingsProvider */
    private $originalProvider;

    public static function setUpBeforeClass(): void
    {
        self::$fixture = new Fixture();
        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = (new ReflectionClass(Controller::class))->newInstanceWithoutConstructor();

        $this->dir = sys_get_temp_dir() . '/matomo-installer-reread-int-' . uniqid('', true);
        mkdir($this->dir, 0777, true);
        $this->localPath = $this->dir . '/config.ini.php';

        // Point the config singleton at a throwaway file so the guards act on it rather than
        // the real test config. The DB connection is already open, so a config with no
        // [database] section does not disturb it.
        $container = StaticContainer::getContainer();
        $this->originalConfig = $container->get('Piwik\Config');
        $this->originalProvider = $container->get(GlobalSettingsProvider::class);

        $this->wireConfigAtLocalPath();
    }

    private function wireConfigAtLocalPath(): void
    {
        $container = StaticContainer::getContainer();
        $provider = new GlobalSettingsProvider(null, $this->localPath);
        $container->set(GlobalSettingsProvider::class, $provider);
        $container->set('Piwik\Config', new Config($provider));
    }

    public function tearDown(): void
    {
        $container = StaticContainer::getContainer();
        $container->set('Piwik\Config', $this->originalConfig);
        $container->set(GlobalSettingsProvider::class, $this->originalProvider);

        foreach ((array) glob($this->dir . '/*') as $file) {
            @unlink($file);
        }
        @rmdir($this->dir);

        parent::tearDown();
    }

    private function invoke(string $method, array $args = [])
    {
        $ref = new ReflectionMethod(Controller::class, $method);
        $ref->setAccessible(true);

        return $ref->invoke($this->controller, ...$args);
    }

    public function testCreateConfigFileWritesOnFreshInstall(): void
    {
        // No config on disk: the guard passes and the installer writes the config normally.
        $this->invoke('createConfigFile', [['username' => 'root', 'password' => '', 'dbname' => 'test', 'host' => '127.0.0.1', 'tables_prefix' => '']]);

        $this->assertFileExists($this->localPath);

        $onDisk = new Config(new GlobalSettingsProvider(null, $this->localPath));
        $this->assertSame('root', $onDisk->database['username']);
        $this->assertEquals(1, $onDisk->General['installation_in_progress']);
    }

    public function testDeleteConfigFileClearsMidInstall(): void
    {
        // Mid-install config on disk: the guard passes and the stale file is cleared.
        file_put_contents(
            $this->localPath,
            "; <?php exit; ?>\n[database]\nusername = \"root\"\n[General]\ninstallation_in_progress = 1\ninstallation_first_accessed = 123\n[Extra]\nmarker = \"deleteme\"\n"
        );
        // Reload the in-memory config from the file just written so it matches disk.
        $this->wireConfigAtLocalPath();

        $this->invoke('deleteConfigFileIfNeeded');

        $onDisk = new Config(new GlobalSettingsProvider(null, $this->localPath));
        $this->assertArrayNotHasKey('marker', (array) $onDisk->Extra);
        $this->assertArrayNotHasKey('username', (array) $onDisk->database);
    }
}
