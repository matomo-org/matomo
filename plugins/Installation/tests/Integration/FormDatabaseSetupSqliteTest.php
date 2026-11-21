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
     * @var string[]
     */
    private $privateDirectoriesBefore;

    /**
     * @var string
     */
    private $directory;

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
        $this->directory = 'tmp/sqlite-form-test-' . bin2hex(random_bytes(4));
        mkdir(PIWIK_USER_PATH . '/' . $this->directory, 0700);

        // a web server that denies every file, or fails
        StaticContainer::getContainer()->set(ExposureProbe::class, new ExposureProbe(function () {
            $status = $this->probedStatus === ExposureProbe::INCONCLUSIVE ? 503 : 403;
            return ['status' => $status, 'body' => '', 'redirected' => false];
        }));
    }

    public function tearDown(): void
    {
        if ($this->post !== null) {
            $_POST = $this->post;
        }

        // the form connects to the database it creates
        Db::destroyDatabaseObject();
        Db::createDatabaseObject();

        foreach (array_diff($this->getPrivateDirectories(), $this->privateDirectoriesBefore ?? []) as $directory) {
            $this->removeDirectory($directory);
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
        $this->assertSame(1, $dbInfos['sqlite_wal_reset_fix_attested']);
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
        StaticContainer::getContainer()->set(ExposureProbe::class, new ExposureProbe(function (string $url, ?array $range) {
            // serves the file the URL names, as a server without deny rules does
            $file = dirname(Config::getInstance()->getLocalPath()) . '/' . implode('/', array_slice(explode('/', $url), -2));
            $content = (string) file_get_contents($file);
            return ['status' => 200, 'body' => $range === null ? $content : substr($content, 0, $range[1] + 1), 'redirected' => false];
        }));

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

    public function testRefusesAnUnqualifiedLibraryUnlessAttested(): void
    {
        $version = Connection::getLibraryVersion();
        if (Connection::getRuntimeQualification($version, false) !== Connection::RUNTIME_UNQUALIFIED) {
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
