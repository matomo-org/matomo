<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class StorageTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', 1);
    }

    public function testConstructorFailsOnInvalidPlugin()
    {
        self::expectException(\Exception::class);

        $storage = new Storage('Inval$dPlü§1n');
    }

    public function testWasRequested()
    {
        $storage = new Storage('PremiumPlugin');
        self::assertFalse($storage->wasRequested());

        $storage->setRequested();
        self::assertTrue($storage->wasRequested());

        // ensure same result with new object
        $storage = new Storage('PremiumPlugin');
        self::assertTrue($storage->wasRequested());
    }

    public function testWasRequestedByCurrentUserWithAnExpiryReachingBeforeTheEarliestDate()
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', 20000);

        $storage = new Storage('PremiumPlugin');
        $storage->setRequested();

        self::assertTrue($storage->wasRequestedByCurrentUser());
    }

    public function testClearStorage()
    {
        // Manually create a request that is 25 hours old
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - (25 * 3600),
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));

        $storage = new Storage('PremiumPlugin');
        $storage->clearStorage();
        self::assertFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
    }

    public function testWasRequestedClearsStorageWhenOutdated()
    {
        // Manually create a request that is 25 hours old
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - (25 * 3600),
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));

        $storage = new Storage('PremiumPlugin');
        self::assertFalse($storage->wasRequested());
        self::assertFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
    }

    public function testDismissRequest()
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested();
        self::assertTrue($storage->wasRequested());
        self::assertFalse($storage->isNotificationDismissed());
        $storage->setNotificationDismissed();
        self::assertTrue($storage->isNotificationDismissed());
    }

    public function testGetPluginsInStorage()
    {
        // Manually create some requests
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - 10,
            'displayName' => 'A useful plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));
        Option::set('Marketplace.PluginTrialRequest.BestPluginEver', json_encode([
            'requestTime' => time(),
            'displayName' => '',
            'dismissed' => ['admin'],
            'requestedBy' => 'peter',
        ]));

        self::assertEquals(['BestPluginEver', 'PremiumPlugin'], Storage::getPluginsInStorage());
    }

    public function testSetRequestedAddsEachRequestToHistory()
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested('Premium Plugin');
        $storage->clearStorage();
        $storage->setRequested('Premium Plugin');

        $requests = (new RequestHistory())->getRequests('PremiumPlugin');
        self::assertCount(2, $requests);
        self::assertSame([Piwik::getCurrentUserLogin(), Piwik::getCurrentUserLogin()], array_column($requests, 'login'));
    }

    public function testSetFulfilledEndsTheRequestInBothOptionAndHistory()
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested('Premium Plugin');
        $storage->setFulfilled();

        self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
        $requests = (new RequestHistory())->getRequests('PremiumPlugin');
        self::assertCount(1, $requests);
        self::assertNotNull($requests[0]['ts_fulfilled']);
        self::assertNull($requests[0]['ts_expired']);
    }

    public function testWasRequestedRecordsTheExpiryInHistory()
    {
        $requestTime = time() - (25 * 3600);
        (new RequestHistory())->add('PremiumPlugin', 'olaf', $requestTime);
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => $requestTime,
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));

        self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
        $requests = (new RequestHistory())->getRequests('PremiumPlugin');
        self::assertNotNull($requests[0]['ts_expired']);
        self::assertNull($requests[0]['ts_fulfilled']);
    }

    public function testSetRequestedKeepsNoHistoryWhenTheOptionCannotBeSaved()
    {
        $storage = new class ('PremiumPlugin') extends Storage {
            protected function saveStorage(): void
            {
                throw new \RuntimeException('option write failed');
            }
        };

        try {
            $storage->setRequested('Premium Plugin');
            self::fail('Expected the failed option write to be rethrown');
        } catch (\RuntimeException $e) {
            self::assertSame('option write failed', $e->getMessage());
        }

        self::assertFalse($storage->wasRequested());
        self::assertSame([], (new RequestHistory())->getRequests('PremiumPlugin'));
    }

    public function testSetFulfilledLeavesTheRequestOpenWhenTheOptionCannotBeCleared()
    {
        $storage = $this->createStorageThatCannotClear();
        $storage->setRequested('Premium Plugin');

        $this->assertOptionClearFailure(fn() => $storage->setFulfilled());

        self::assertNotFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
        self::assertSame([null], array_column((new RequestHistory())->getRequests('PremiumPlugin'), 'ts_fulfilled'));
    }

    public function testExpiryLeavesTheRequestOpenWhenTheOptionCannotBeCleared()
    {
        $requestTime = time() - (25 * 3600);
        (new RequestHistory())->add('PremiumPlugin', 'olaf', $requestTime);
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => $requestTime,
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));

        $storage = $this->createStorageThatCannotClear();
        $this->assertOptionClearFailure(fn() => $storage->wasRequested());

        self::assertNotFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
        self::assertSame([null], array_column((new RequestHistory())->getRequests('PremiumPlugin'), 'ts_expired'));
    }

    public function testExpiryKeepsARequestThatReplacedTheOutdatedOneSinceItWasLoaded()
    {
        $outdatedTime = time() - (25 * 3600);
        (new RequestHistory())->add('PremiumPlugin', 'olaf', $outdatedTime);
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => $outdatedTime,
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));
        $staleStorage = new Storage('PremiumPlugin');

        // another process expires the outdated request and makes a new one
        self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
        (new Storage('PremiumPlugin'))->setRequested('Premium Plugin');

        self::assertTrue($staleStorage->wasRequested());
        self::assertTrue((new Storage('PremiumPlugin'))->wasRequested());
        $requests = (new RequestHistory())->getRequests('PremiumPlugin');
        self::assertSame([null, null], array_column($requests, 'ts_fulfilled'));
        self::assertNull($requests[0]['ts_expired']);
        self::assertNotNull($requests[1]['ts_expired']);
    }

    public function testExpiryDoesNothingWhenTheOutdatedRequestIsAlreadyGone()
    {
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - (25 * 3600),
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));
        $staleStorage = new Storage('PremiumPlugin');
        Option::delete('Marketplace.PluginTrialRequest.PremiumPlugin');

        self::assertFalse($staleStorage->wasRequested());
        self::assertFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
    }

    public function testDeletingUserRemovesTheirLoginFromPendingRequestsAndHistory()
    {
        Fixture::createSuperUser();
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('bob', 'secret-password-2', 'bob@example.com');

        (new RequestHistory())->add('PremiumPlugin', 'alice', time());
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time(),
            'displayName' => 'Premium Plugin',
            'dismissed' => ['bob', 'alice'],
            'requestedBy' => 'alice',
        ]));
        Option::set('Marketplace.PluginTrialRequest.OtherPlugin', json_encode([
            'requestTime' => time(),
            'displayName' => 'Other Plugin',
            'dismissed' => ['alice'],
            'requestedBy' => 'bob',
        ]));

        UsersManagerAPI::getInstance()->deleteUser('alice');

        $premium = json_decode(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'), true);
        self::assertNull($premium['requestedBy']);
        self::assertSame(['bob'], $premium['dismissed']);

        $other = json_decode(Option::get('Marketplace.PluginTrialRequest.OtherPlugin'), true);
        self::assertSame('bob', $other['requestedBy']);
        self::assertSame([], $other['dismissed']);

        self::assertSame([null], array_column((new RequestHistory())->getRequests('PremiumPlugin'), 'login'));
    }

    public function testAnonymizingKeepsARequestThatReplacedTheLoadedOne()
    {
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - 3600,
            'displayName' => 'Premium Plugin',
            'dismissed' => ['alice'],
            'requestedBy' => 'alice',
        ]));
        $staleStorage = new Storage('PremiumPlugin');

        $newRequest = [
            'requestTime' => time(),
            'displayName' => 'Premium Plugin',
            'dismissed' => ['alice'],
            'requestedBy' => 'bob',
        ];
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode($newRequest));

        $staleStorage->anonymizeLogin('alice');

        $stored = json_decode(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'), true);
        self::assertSame(array_merge($newRequest, ['dismissed' => []]), $stored);
    }

    public function testDismissingKeepsALoginAnonymisedSinceItWasLoaded()
    {
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time(),
            'displayName' => 'Premium Plugin',
            'dismissed' => ['alice'],
            'requestedBy' => 'alice',
        ]));
        $staleStorage = new Storage('PremiumPlugin');

        (new Storage('PremiumPlugin'))->anonymizeLogin('alice');
        $staleStorage->setNotificationDismissed();

        $stored = json_decode(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'), true);
        self::assertNull($stored['requestedBy']);
        self::assertSame([Piwik::getCurrentUserLogin()], $stored['dismissed']);
    }

    public function testDismissingDoesNotRestoreARequestRemovedSinceItWasLoaded()
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested('Premium Plugin');
        Option::delete('Marketplace.PluginTrialRequest.PremiumPlugin');

        $storage->setNotificationDismissed();

        self::assertFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
    }

    public function testRequestsKeepWorkingBeforeTheUpdateCreatesTheHistoryTable()
    {
        Db::query('DROP TABLE ' . Common::prefixTable(RequestHistory::TABLE_NAME));

        try {
            $storage = new Storage('PremiumPlugin');
            $storage->setRequested('Premium Plugin');
            self::assertTrue((new Storage('PremiumPlugin'))->wasRequested());
            self::assertTrue((new Storage('PremiumPlugin'))->wasRequestedByCurrentUser());

            Option::set('Marketplace.PluginTrialRequest.OtherPlugin', json_encode([
                'requestTime' => time() - (25 * 3600),
                'displayName' => 'Other Plugin',
                'dismissed' => [],
                'requestedBy' => 'olaf',
            ]));
            self::assertFalse((new Storage('OtherPlugin'))->wasRequested());
            self::assertFalse(Option::get('Marketplace.PluginTrialRequest.OtherPlugin'));

            $storage->setFulfilled();
            self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
        } finally {
            DbHelper::createTables();
        }
    }

    private function createStorageThatCannotClear(): Storage
    {
        return new class ('PremiumPlugin') extends Storage {
            public function clearStorage(): void
            {
                throw new \RuntimeException('option delete failed');
            }
        };
    }

    private function assertOptionClearFailure(callable $write): void
    {
        try {
            $write();
            self::fail('Expected the failed option delete to be rethrown');
        } catch (\RuntimeException $e) {
            self::assertSame('option delete failed', $e->getMessage());
        }
    }
}
