<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Session\SaveHandler;

use Piwik\Common;
use Piwik\Session;
use Piwik\Session\SaveHandler\DbTable;
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

    public function testWriteKeepsValuesStoredByAnOverlappingRequest()
    {
        $this->testInstance->write('testid', serialize(['user.name' => 'admin']));

        $slowRequest = new DbTable(Session::getDbTableConfig());
        $slowRequest->read('testid');

        $nonceRequest = new DbTable(Session::getDbTableConfig());
        $nonceRequest->read('testid');
        $nonceRequest->write('testid', serialize([
            'user.name' => 'admin',
            'Feedback.sendFeedback' => ['nonce' => 'thenonce'],
            '__ZF' => ['Feedback.sendFeedback' => ['ENT' => ['nonce' => 100]]],
        ]));

        $slowRequest->write('testid', serialize([
            'user.name' => 'admin',
            'session.info' => ['expiration' => 100],
        ]));

        $storedValues = Common::safe_unserialize($this->testInstance->read('testid'));

        $this->assertSame([
            'user.name' => 'admin',
            'Feedback.sendFeedback' => ['nonce' => 'thenonce'],
            '__ZF' => ['Feedback.sendFeedback' => ['ENT' => ['nonce' => 100]]],
            'session.info' => ['expiration' => 100],
        ], $storedValues);
    }

    public function testWriteAppliesValuesRemovedByTheCurrentRequest()
    {
        $this->testInstance->write('testid', serialize([
            'user.name' => 'admin',
            'Feedback.sendFeedback' => ['nonce' => 'thenonce'],
        ]));

        $discardingRequest = new DbTable(Session::getDbTableConfig());
        $discardingRequest->read('testid');

        $otherRequest = new DbTable(Session::getDbTableConfig());
        $otherRequest->read('testid');
        $otherRequest->write('testid', serialize([
            'user.name' => 'admin',
            'Feedback.sendFeedback' => ['nonce' => 'thenonce'],
            'session.info' => ['expiration' => 100],
        ]));

        $discardingRequest->write('testid', serialize(['user.name' => 'admin']));

        $storedValues = Common::safe_unserialize($this->testInstance->read('testid'));

        $this->assertSame([
            'user.name' => 'admin',
            'session.info' => ['expiration' => 100],
        ], $storedValues);
    }
}
