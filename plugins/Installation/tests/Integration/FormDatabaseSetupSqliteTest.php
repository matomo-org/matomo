<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Installation\tests\Integration;

use PDO;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ExposureProbe;
use Piwik\Plugins\Installation\FormDatabaseSetup;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The database setup form with SQLite chosen. Runs on every test database: the form only needs pdo_sqlite.
 *
 * @group Installation
 */
class FormDatabaseSetupSqliteTest extends IntegrationTestCase
{
    /**
     * @var array
     */
    private $post;

    /**
     * @var string[]|null set once setUp() completes: a skipped test cleans nothing
     */
    private $privateDirectoriesBefore;

    /**
     * @var string|null
     */
    private $directory;

    /**
     * @var int|null
     */
    private $umask;

    /**
     * @var string
     */
    private $probedStatus = ExposureProbe::PROTECTED;

    public function setUp(): void
    {
        parent::setUp();

        if (!Connection::isEnabled()) {
            $this->markTestSkipped('pdo_sqlite is not available');
        }

        Fixture::loadAllTranslations();
        $this->post = $_POST;
        $this->privateDirectoriesBefore = $this->getPrivateDirectories();
        $directory = 'tmp/sqlite-form-test-' . bin2hex(random_bytes(4));
        mkdir(PIWIK_USER_PATH . '/' . $directory, 0700);
        $this->directory = $directory;
        // a permissive umask: the files must be private regardless
        $this->umask = umask(0);

        // a web server that serves Matomo and denies every other file, or fails
        $this->setWebServer(function () {
            return ['status' => $this->probedStatus === ExposureProbe::INCONCLUSIVE ? 503 : 403, 'body' => ''];
        });
    }

    public function tearDown(): void
    {
        if ($this->post !== null) {
            $_POST = $this->post;
        }

        // the form connects to the database it creates
        Db::destroyDatabaseObject();
        Db::createDatabaseObject();

        if ($this->umask !== null) {
            umask($this->umask);
        }
        if ($this->privateDirectoriesBefore !== null) {
            foreach (array_diff($this->getPrivateDirectories(), $this->privateDirectoriesBefore) as $directory) {
                $this->removeDirectory($directory);
            }
        }
        if ($this->directory !== null) {
            $this->removeDirectory(PIWIK_USER_PATH . '/' . $this->directory);
        }

        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function testCreatesTheDatabaseInANewPrivateDirectory(): void
    {
        $form = $this->submit(['sqlite_wal_reset_fix_attested' => '1']);
        $this->assertTrue($form->validate(), 'no server, login or database name is needed');

        $dbInfos = $form->createDatabaseObject();

        $configDirectory = dirname(Config::getInstance()->getLocalPath());
        $this->assertMatchesRegularExpression('~(^|/)\.ht\.[0-9a-f]{32}/matomo\.sqlite$~', $dbInfos['dbname']);
        $file = Connection::getDatabaseFile($dbInfos);
        $this->assertSame(realpath($configDirectory), realpath(dirname($file, 2)));
        $this->assertSame(0700, fileperms(dirname($file)) & 0777);
        $this->assertSame(0600, fileperms($file) & 0777);
        $this->assertSame('PDO\SQLITE', $dbInfos['adapter']);
        $this->assertSame('Sqlite', $dbInfos['schema']);
        if ($this->isQualified()) {
            $this->assertArrayNotHasKey('sqlite_wal_reset_fix_attested', $dbInfos, 'the form has no attestation to give');
        } else {
            $this->assertSame(1, $dbInfos['sqlite_wal_reset_fix_attested']);
        }
        $this->assertSame('wal', (new PDO('sqlite:' . $file))->query('PRAGMA journal_mode')->fetchColumn());
        $this->assertTrue(Connection::isSqliteConnection(Db::get()));
    }

    public function testKeepsAGivenPath(): void
    {
        $path = $this->directory . '/analytics.sqlite';
        $dbInfos = $this->submit(['sqlite_file' => $path, 'sqlite_wal_reset_fix_attested' => '1'])->createDatabaseObject();

        $this->assertSame($path, $dbInfos['dbname']);
        $this->assertSame(0600, fileperms(PIWIK_USER_PATH . '/' . $path) & 0777);
        $this->assertSame([], array_diff($this->getPrivateDirectories(), $this->privateDirectoriesBefore));
    }

    public function testRefusesAServedLocationAndRemovesWhatItCreated(): void
    {
        $this->setWebServer(function (string $url) {
            // serves the file the URL names, as a server without deny rules does
            $file = dirname(Config::getInstance()->getLocalPath()) . '/' . implode('/', array_slice(explode('/', $url), -2));
            return ['status' => 200, 'body' => (string) file_get_contents($file)];
        });

        try {
            $this->submit(['sqlite_wal_reset_fix_attested' => '1'])->createDatabaseObject();
            $this->fail('the location is refused');
        } catch (\Exception $e) {
            $this->assertStringContainsString('anyone could download', $e->getMessage());
        }

        $this->assertSame([], array_diff($this->getPrivateDirectories(), $this->privateDirectoriesBefore), 'the new directory is removed');
    }

    public function testRefusesALocationNotProvenProtected(): void
    {
        $this->probedStatus = ExposureProbe::INCONCLUSIVE;
        $path = $this->directory . '/analytics.sqlite';

        try {
            $this->submit(['sqlite_file' => $path, 'sqlite_wal_reset_fix_attested' => '1'])->createDatabaseObject();
            $this->fail('the location is refused');
        } catch (\Exception $e) {
            $this->assertStringContainsString('401, 403 or 404', $e->getMessage());
        }

        $this->assertSame([], glob(PIWIK_USER_PATH . '/' . $this->directory . '/*'), 'the new file is removed');
    }

    public function testRefusesStaleWalFilesForANewDatabase(): void
    {
        $path = $this->directory . '/analytics.sqlite';
        touch(PIWIK_USER_PATH . '/' . $path . '-wal');

        try {
            $this->submit(['sqlite_file' => $path, 'sqlite_wal_reset_fix_attested' => '1'])->createDatabaseObject();
            $this->fail('the stale file is refused');
        } catch (\Exception $e) {
            $this->assertStringContainsString('already exists without its database', $e->getMessage());
        }

        $this->assertFileExists(PIWIK_USER_PATH . '/' . $path . '-wal', 'a file it did not create is kept');
        $this->assertFileDoesNotExist(PIWIK_USER_PATH . '/' . $path);
    }

    public function testRefusesAnExistingFileOthersCanRead(): void
    {
        $path = $this->directory . '/analytics.sqlite';
        touch(PIWIK_USER_PATH . '/' . $path);
        chmod(PIWIK_USER_PATH . '/' . $path, 0644);

        try {
            $this->submit(['sqlite_file' => $path, 'sqlite_wal_reset_fix_attested' => '1'])->createDatabaseObject();
            $this->fail('the file is refused');
        } catch (\Exception $e) {
            $this->assertStringContainsString('chmod 600', $e->getMessage());
        }
    }

    public function testNeverRemovesAnExistingDatabase(): void
    {
        $this->probedStatus = ExposureProbe::INCONCLUSIVE;
        $path = $this->directory . '/analytics.sqlite';
        $file = PIWIK_USER_PATH . '/' . $path;
        $pdo = new PDO('sqlite:' . $file);
        $pdo->exec('CREATE TABLE kept (a INTEGER)');
        $pdo = null;
        chmod($file, 0600);

        try {
            $this->submit(['sqlite_file' => $path, 'sqlite_wal_reset_fix_attested' => '1'])->createDatabaseObject();
            $this->fail('the location is refused');
        } catch (\Exception $e) {
            $this->assertStringContainsString('401, 403 or 404', $e->getMessage());
        }

        $this->assertSame('kept', (new PDO('sqlite:' . $file))->query("SELECT name FROM sqlite_master WHERE name = 'kept'")->fetchColumn());
    }

    public function testRefusesAnUnqualifiedLibraryUnlessAttested(): void
    {
        $version = Connection::getLibraryVersion();
        if ($this->isQualified()) {
            $this->markTestSkipped("SQLite $version includes the WAL-reset fix");
        }

        $form = $this->submit([]);
        $this->assertNotEmpty($form->getElementsByName('sqlite_wal_reset_fix_attested'), 'the form offers the attestation');

        try {
            $form->createDatabaseObject();
            $this->fail('the library is refused');
        } catch (\Exception $e) {
            $this->assertStringContainsString($version . ' may lack the fix', $e->getMessage());
        }
        $this->assertSame([], array_diff($this->getPrivateDirectories(), $this->privateDirectoriesBefore));
    }

    public function testServerFieldsAreStillRequiredForMysql(): void
    {
        $form = $this->submit(['adapter' => 'PDO\MYSQL']);

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getElementsByName('host')[0]->getError());
        $this->assertNotEmpty($form->getElementsByName('dbname')[0]->getError());
    }

    private function submit(array $values): FormDatabaseSetup
    {
        $_POST = array_merge([
            'host' => '',
            'username' => '',
            'password' => '',
            'dbname' => '',
            'tables_prefix' => 'matomo_',
            'adapter' => 'PDO\SQLITE',
            'schema' => 'Mysql',
            'sqlite_file' => '',
        ], $values);

        return new FormDatabaseSetup();
    }

    /**
     * Requests to Matomo's own matomo.js succeed; every other URL gets $respond's answer.
     */
    private function setWebServer(callable $respond): void
    {
        StaticContainer::getContainer()->set(ExposureProbe::class, new ExposureProbe(function (string $url) use ($respond) {
            if (str_ends_with($url, '/matomo.js')) {
                return ['status' => 200, 'body' => (string) file_get_contents(PIWIK_INCLUDE_PATH . '/matomo.js', false, null, 0, 64)];
            }
            return $respond($url);
        }));
    }

    private function isQualified(): bool
    {
        return Connection::getRuntimeQualification(Connection::getLibraryVersion(), false) !== Connection::RUNTIME_UNQUALIFIED;
    }

    /**
     * @return string[]
     */
    private function getPrivateDirectories(): array
    {
        return glob(dirname(Config::getInstance()->getLocalPath()) . '/.ht.*', GLOB_ONLYDIR) ?: [];
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (glob($directory . '/{,.}*[!.]*', GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($directory);
    }
}
