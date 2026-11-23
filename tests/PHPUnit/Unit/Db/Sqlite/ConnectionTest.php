<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Sqlite;

use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use Piwik\Db\Sqlite\Connection;

class ConnectionTest extends TestCase
{
    use RequiresSqlite;

    public function setUp(): void
    {
        parent::setUp();
        $this->requireSqlite();
    }

    /**
     * @var string|null
     */
    private $directory;

    public function tearDown(): void
    {
        if ($this->directory !== null) {
            foreach (glob($this->directory . '/*') as $file) {
                unlink($file);
            }
            rmdir($this->directory);
        }
        parent::tearDown();
    }

    /**
     * @dataProvider getInvalidDatabaseNames
     */
    public function testDatabaseNameMustBeAFilePath(string $dbname): void
    {
        $this->expectException(Exception::class);
        Connection::getDatabaseFile(['dbname' => $dbname]);
    }

    public function getInvalidDatabaseNames(): iterable
    {
        yield 'empty' => [' '];
        yield 'in memory' => [':memory:'];
        yield 'URI' => ['file:matomo.db?mode=memory'];
    }

    public function testRelativeDatabaseFileIsResolvedAgainstTheUserPath(): void
    {
        $this->assertSame('/var/lib/matomo.db', Connection::getDatabaseFile(['dbname' => '/var/lib/matomo.db']));
        $this->assertSame(
            rtrim(PIWIK_USER_PATH, '/\\') . DIRECTORY_SEPARATOR . 'tmp/matomo.db',
            Connection::getDatabaseFile(['dbname' => 'tmp/matomo.db'])
        );
    }

    /**
     * @dataProvider getAdapterNames
     */
    public function testIsSqliteAdapter(string $adapter, bool $expected): void
    {
        $this->assertSame($expected, Connection::isSqliteAdapter($adapter));
    }

    public function getAdapterNames(): iterable
    {
        yield ['PDO\SQLITE', true];
        yield ['pdo_sqlite', true];
        yield ['Pdo\Sqlite', true];
        yield ['PDO SQLITE', true];
        yield [' PDO\SQLITE', false]; // nor these
        yield ['PDO\\\\SQLITE', false];
        yield ['pdo/sqlite', false]; // the adapter factories don't accept it
        yield ['PDO\MYSQL', false];
        yield ['MYSQLI', false];
    }

    public function testOpeningAMissingFileFailsInsteadOfCreatingIt(): void
    {
        $config = ['dbname' => $this->getDirectory() . '/missing.db'];

        try {
            Connection::open($config);
            $this->fail('no exception');
        } catch (Exception $e) {
            $this->assertStringContainsString('does not exist', $e->getMessage());
        }
        $this->assertFileDoesNotExist($config['dbname']);
    }

    public function testCreatedDatabaseUsesWalAndConnectionsAreConfigured(): void
    {
        $config = ['dbname' => $this->getDirectory() . '/matomo.db', 'sqlite_busy_timeout' => 1234];
        Connection::createDatabaseFile($config);

        $pdo = Connection::open($config);

        $this->assertSame('wal', $pdo->query('PRAGMA journal_mode')->fetchColumn());
        $this->assertSame(1234, (int) $pdo->query('PRAGMA busy_timeout')->fetchColumn());
        $this->assertSame(2, (int) $pdo->query('PRAGMA synchronous')->fetchColumn()); // FULL
        $this->assertSame(Connection::JOURNAL_SIZE_LIMIT_BYTES, (int) $pdo->query('PRAGMA journal_size_limit')->fetchColumn());

        $pdo = null;
        Connection::deleteDatabaseFile($config);
        $this->assertSame([], glob($this->directory . '/*'));
    }

    public function testParametersAreBoundWithTheTypesOfTheirValues(): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // an expression has no type affinity, so a parameter bound as text is never equal to a number
        $statement = $pdo->prepare('SELECT 2 + 3 >= ?, 1 = ?, ? IS NULL, ?, typeof(?)');
        Connection::bindValues($statement, [5, true, null, 'x', 'y']);
        $statement->execute();
        $this->assertSame([1, 1, 1, 'x', 'text'], array_map(function ($value) {
            return is_numeric($value) ? (int) $value : $value;
        }, $statement->fetch(PDO::FETCH_NUM)));

        // keys are ignored for positional parameters, as by PDOStatement::execute()
        $statement = $pdo->prepare('SELECT ? || ?');
        Connection::bindValues($statement, [3 => 'a', 7 => 'b']);
        $statement->execute();
        $this->assertSame('ab', $statement->fetchColumn());

        $statement = $pdo->prepare('SELECT :a + :b');
        Connection::bindValues($statement, ['a' => 1, ':b' => 2]);
        $statement->execute();
        $this->assertSame(3, (int) $statement->fetchColumn());
    }


    /**
     * @dataProvider getRuntimeQualifications
     */
    public function testRuntimeQualification(string $version, bool $isAttested, string $expected): void
    {
        $this->assertSame($expected, Connection::getRuntimeQualification($version, $isAttested));
    }

    public function getRuntimeQualifications(): iterable
    {
        yield 'below the floor' => ['3.43.2', false, Connection::RUNTIME_UNSUPPORTED];
        yield 'below the floor, attested' => ['3.43.2', true, Connection::RUNTIME_UNSUPPORTED];
        yield '3.44 before the fix' => ['3.44.5', false, Connection::RUNTIME_UNQUALIFIED];
        yield '3.44 with the fix' => ['3.44.6', false, Connection::RUNTIME_QUALIFIED];
        yield 'branch without a fix' => ['3.46.1', false, Connection::RUNTIME_UNQUALIFIED];
        yield 'branch without a fix, attested' => ['3.46.1', true, Connection::RUNTIME_QUALIFIED];
        yield '3.50 before the fix' => ['3.50.6', false, Connection::RUNTIME_UNQUALIFIED];
        yield '3.50 with the fix' => ['3.50.7', false, Connection::RUNTIME_QUALIFIED];
        yield '3.51 before the fix' => ['3.51.2', false, Connection::RUNTIME_UNQUALIFIED];
        yield '3.51 with the fix' => ['3.51.3', false, Connection::RUNTIME_QUALIFIED];
        yield 'later release' => ['3.52.0', false, Connection::RUNTIME_QUALIFIED];
    }
    private function getDirectory(): string
    {
        $this->directory = sys_get_temp_dir() . '/matomo-sqlite-test-' . bin2hex(random_bytes(4));
        mkdir($this->directory);

        return $this->directory;
    }
}
