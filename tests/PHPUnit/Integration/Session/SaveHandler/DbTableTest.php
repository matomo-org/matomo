<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Session\SaveHandler;

use Piwik\Common;
use Piwik\Db;
use Piwik\SettingsPiwik;
use Piwik\Session;
use Piwik\Session\SaveHandler\DbTable;
use Piwik\Session\SaveHandler\SessionDataMerger;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class DbTableTest extends IntegrationTestCase
{
    /**
     * @var DbTable
     */
    private $testInstance;

    public function setUp(): void
    {
        parent::setUp();
        $this->testInstance = new DbTable(Session::getDbTableConfig());
    }

    public function testReadReturnsTheSessionDataCorrectly()
    {
        $this->testInstance->write('testid', 'testdata');

        $result = $this->testInstance->read('testid');

        $this->assertEquals('testdata', $result);
    }

    public function testWriteKeepsStoredDataWhenThisRequestChangedNothing()
    {
        $this->testInstance->write('testid', 'firstdata');

        $unchanged = new DbTable(Session::getDbTableConfig());
        $unchanged->read('testid');

        $other = new DbTable(Session::getDbTableConfig());
        $other->read('testid');
        $other->write('testid', 'seconddata');

        $unchanged->write('testid', 'firstdata');

        $this->assertEquals('seconddata', $this->testInstance->read('testid'));
    }

    public function testWriteStillRefreshesTheSessionWhenThisRequestChangedNothing()
    {
        $this->testInstance->write('testid', 'testdata');
        $this->testInstance->read('testid');

        // back-date it so a refreshed timestamp is actually distinguishable
        $this->setModified('testid', time() - 120);

        $this->testInstance->write('testid', 'testdata');

        $this->assertGreaterThan(time() - 120, $this->getModified('testid'));
        $this->assertEquals('testdata', $this->testInstance->read('testid'));
    }

    public function testWriteKeepsTheSessionAliveWhenTheRowWasRemovedAndNothingChanged()
    {
        $this->testInstance->write('testid', 'testdata');
        $this->testInstance->read('testid');

        $other = new DbTable(Session::getDbTableConfig());
        $other->destroy('testid');

        $this->testInstance->write('testid', 'testdata');

        $this->assertEquals('testdata', $this->testInstance->read('testid'));
    }

    public function testWriteKeepsTheSessionAliveWhenTheRowWasRemovedAfterAChange()
    {
        $this->testInstance->write('testid', 'testdata');
        $this->testInstance->read('testid');

        $other = new DbTable(Session::getDbTableConfig());
        $other->destroy('testid');

        // unlike the test above this one has something to store, so it goes through the merge
        $this->testInstance->write('testid', 'changeddata');

        $this->assertEquals('changeddata', $this->testInstance->read('testid'));
    }

    /**
     * The data column is not case sensitive, so comparing it without casting to binary would treat
     * a value another request stored as the one this request read, and skip the merge entirely.
     */
    public function testWriteComparesTheStoredDataAsBytes()
    {
        $merger = new class extends SessionDataMerger {
            public function merge(string $base, string $mine, string $theirs): string
            {
                return 'mergedvalue';
            }
        };

        $this->testInstance->write('testid', 'storeddata');

        $mine = new DbTable(Session::getDbTableConfig(), $merger);
        $mine->read('testid');

        // another request stored something this one never read, differing only in letter case
        $this->setData('testid', 'STOREDDATA');

        $mine->write('testid', 'minedata');

        $this->assertSame('mergedvalue', $this->rawStored('testid'));
    }

    /**
     * A row whose data column is null holds nothing the write can compare against, so every attempt
     * fails the same way. It has to give up retrying and store the session as it is.
     */
    public function testWriteStopsRetryingWhenTheStoredDataCanNeverBeMatched()
    {
        $session = ['user.name' => 'chip', 'Dashboard' => ['layout' => 'x']];

        $this->testInstance->write('testid', $this->encode($session));
        $this->setData('testid', null);

        $mine = new DbTable(Session::getDbTableConfig());
        $this->assertSame('', $mine->read('testid'));

        $this->assertTrue($mine->write('testid', $this->encode($session)));
        $this->assertSame($session, $this->decodeStored('testid'));
    }

    public function testWriteStoresTheLastValueWhenWrittenTwice()
    {
        $this->testInstance->write('testid', 'firstdata');
        $this->testInstance->read('testid');

        $this->testInstance->write('testid', 'seconddata');
        $this->testInstance->write('testid', 'firstdata');

        $this->assertEquals('firstdata', $this->testInstance->read('testid'));
    }

    public function testWriteStoresDataThisRequestChanged()
    {
        $this->testInstance->write('testid', 'testdata');
        $this->testInstance->read('testid');

        $this->testInstance->write('testid', 'changeddata');

        $this->assertEquals('changeddata', $this->testInstance->read('testid'));
    }

    public function testConcurrentWritesKeepEachOthersState()
    {
        $this->testInstance->write('testid', $this->encode(['user.name' => 'chip']));

        // both requests read the same session, before either of them has written
        $adding = new DbTable(Session::getDbTableConfig());
        $adding->read('testid');
        $stale = new DbTable(Session::getDbTableConfig());
        $stale->read('testid');

        $adding->write('testid', $this->encode(['user.name' => 'chip', 'Login.login' => ['nonce' => 'abc']]));
        $stale->write('testid', $this->encode(['user.name' => 'chip', 'Dashboard' => ['layout' => 'x']]));

        $stored = $this->decodeStored('testid');

        $this->assertSame(['nonce' => 'abc'], $stored['Login.login'] ?? null);
        $this->assertSame(['layout' => 'x'], $stored['Dashboard'] ?? null);
    }

    public function testConsumedNonceStaysConsumedWhenAnUnrelatedRequestWritesLater()
    {
        $this->testInstance->write('testid', $this->encode(['user.name' => 'chip', 'Login.login' => ['nonce' => 'abc']]));

        $consuming = new DbTable(Session::getDbTableConfig());
        $consuming->read('testid');
        $stale = new DbTable(Session::getDbTableConfig());
        $stale->read('testid');

        // the nonce is checked and discarded, which unsets the whole namespace
        $consuming->write('testid', $this->encode(['user.name' => 'chip']));
        $stale->write('testid', $this->encode(['user.name' => 'chip', 'Login.login' => ['nonce' => 'abc'], 'Dashboard' => ['layout' => 'x']]));

        $stored = $this->decodeStored('testid');

        $this->assertArrayNotHasKey('Login.login', $stored);
        $this->assertSame(['layout' => 'x'], $stored['Dashboard'] ?? null);
    }

    public function testConcurrentFirstWritesKeepEachOthersState()
    {
        // neither request finds a stored session - a stale cookie whose row is gone
        $adding = new DbTable(Session::getDbTableConfig());
        $this->assertSame('', $adding->read('testid'));
        $stale = new DbTable(Session::getDbTableConfig());
        $this->assertSame('', $stale->read('testid'));

        $adding->write('testid', $this->encode(['Login.login' => ['nonce' => 'abc']]));
        $stale->write('testid', $this->encode(['Dashboard' => ['layout' => 'x']]));

        $stored = $this->decodeStored('testid');

        $this->assertSame(['nonce' => 'abc'], $stored['Login.login'] ?? null);
        $this->assertSame(['layout' => 'x'], $stored['Dashboard'] ?? null);
    }

    public function testFirstWriteRethrowsDatabaseErrorsOtherThanDuplicateEntries()
    {
        $table = Common::prefixTable('session_write_error_test');

        Db::exec(
            "CREATE TEMPORARY TABLE `$table` (
                id VARCHAR(128) NOT NULL,
                modified INT NOT NULL,
                lifetime INT NOT NULL,
                data MEDIUMTEXT NOT NULL,
                PRIMARY KEY (id)
            )"
        );

        $config = Session::getDbTableConfig();
        $config['name'] = $table;
        $config['dataColumn'] = 'missing_data';
        $handler = new DbTable($config);

        $this->expectException(\Exception::class);

        try {
            $handler->write('testid', 'testdata');
        } finally {
            Db::exec("DROP TEMPORARY TABLE IF EXISTS `$table`");
        }
    }

    public function testWriteReplacesAnExpiredSessionInsteadOfMergingWithIt()
    {
        $this->testInstance->write('testid', $this->encode(['user.name' => 'chip', 'Dashboard' => ['layout' => 'x']]));

        // the stored session has run out, so reading it finds nothing
        $this->setModified('testid', time() - (2 * (int) ini_get('session.gc_maxlifetime')) - 60);

        $starting = new DbTable(Session::getDbTableConfig());
        $this->assertSame('', $starting->read('testid'));

        $starting->write('testid', $this->encode(['Login.login' => ['nonce' => 'abc']]));

        $this->assertSame(['Login.login' => ['nonce' => 'abc']], $this->decodeStored('testid'));
    }

    public function testWriteSucceedsWhenAnotherRequestAlreadyStoredTheSameValue()
    {
        $this->testInstance->write('testid', $this->encode(['user.name' => 'chip']));

        $mine = new DbTable(Session::getDbTableConfig());
        $mine->read('testid');

        $same = $this->encode(['user.name' => 'chip', 'Dashboard' => ['layout' => 'x']]);

        $other = new DbTable(Session::getDbTableConfig());
        $other->read('testid');
        $other->write('testid', $same);

        $this->assertTrue($mine->write('testid', $same));
        $this->assertSame(['user.name' => 'chip', 'Dashboard' => ['layout' => 'x']], $this->decodeStored('testid'));
    }

    public function testWriteKeepsTheNewestValueWhenTheStoredSessionCannotBeRead()
    {
        // nothing here is a session Matomo wrote, so there is nothing to merge and the behaviour
        // falls back to storing whatever was written last
        $this->testInstance->write('testid', 'firstdata');

        $stale = new DbTable(Session::getDbTableConfig());
        $stale->read('testid');

        $other = new DbTable(Session::getDbTableConfig());
        $other->read('testid');
        $other->write('testid', 'seconddata');

        $stale->write('testid', 'thirddata');

        $this->assertEquals('thirddata', $this->testInstance->read('testid'));
    }

    /**
     * Everything else builds the stored value with buildSessionData(). This checks that is really
     * what PHP hands the handler, so the merge cannot be reading a shape that never occurs.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStoresWhatPhpWritesInAFormTheMergerCanRead()
    {
        ini_set('session.serialize_handler', 'php_serialize');
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');

        $session = ['user.name' => 'chip', 'Login.login' => ['nonce' => 'abc']];

        session_set_save_handler(new DbTable(Session::getDbTableConfig()), false);
        session_id('phpwritten');
        session_start();
        $_SESSION = \Zend_Session::buildSessionData($session);
        session_write_close();

        $merger = new SessionDataMerger();

        $this->assertSame($session, $merger->decode($this->rawStored('phpwritten')));
    }

    /**
     * Builds a payload the way the session handler really receives one: Matomo forces
     * session.serialize_handler to php_serialize, so PHP hands write() serialize() of the
     * envelope Zend_Session wraps $_SESSION in.
     */
    private function encode(array $session)
    {
        return serialize(\Zend_Session::buildSessionData($session));
    }

    private function decodeStored($id)
    {
        $envelope = unserialize($this->rawStored($id));

        return unserialize(base64_decode($envelope['data']));
    }

    private function rawStored($id)
    {
        $config = Session::getDbTableConfig();

        return Db::fetchOne(
            'SELECT ' . $config['dataColumn'] . ' FROM ' . $config['name']
                . ' WHERE ' . $config['primary'] . ' = ?',
            [hash(DbTable::TOKEN_HASH_ALGO, $id . SettingsPiwik::getSalt())]
        );
    }

    private function getModified($id)
    {
        $config = Session::getDbTableConfig();

        return Db::fetchOne(
            'SELECT ' . $config['modifiedColumn'] . ' FROM ' . $config['name']
                . ' WHERE ' . $config['primary'] . ' = ?',
            [hash(DbTable::TOKEN_HASH_ALGO, $id . SettingsPiwik::getSalt())]
        );
    }

    private function setData($id, $data)
    {
        $config = Session::getDbTableConfig();

        Db::query(
            'UPDATE ' . $config['name'] . ' SET ' . $config['dataColumn'] . ' = ?'
                . ' WHERE ' . $config['primary'] . ' = ?',
            [$data, hash(DbTable::TOKEN_HASH_ALGO, $id . SettingsPiwik::getSalt())]
        );
    }

    private function setModified($id, $modified)
    {
        $config = Session::getDbTableConfig();

        Db::query(
            'UPDATE ' . $config['name'] . ' SET ' . $config['modifiedColumn'] . ' = ?'
                . ' WHERE ' . $config['primary'] . ' = ?',
            [$modified, hash(DbTable::TOKEN_HASH_ALGO, $id . SettingsPiwik::getSalt())]
        );
    }
}
