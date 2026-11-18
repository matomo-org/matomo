<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 */
class RequestHistoryTest extends IntegrationTestCase
{
    private RequestHistory $history;

    public function setUp(): void
    {
        parent::setUp();

        $this->history = new RequestHistory();
    }

    public function testGetRequestsReturnsOnlyThatPluginsRequestsNewestFirst(): void
    {
        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('OtherPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('PremiumPlugin', 'bob', strtotime('2026-10-03 10:00:00'));

        self::assertSame([
            ['plugin_name' => 'PremiumPlugin', 'login' => 'bob', 'ts_requested' => '2026-10-03 10:00:00'],
            ['plugin_name' => 'PremiumPlugin', 'login' => 'alice', 'ts_requested' => '2026-10-01 10:00:00'],
        ], $this->history->getRequests('PremiumPlugin'));
    }

    public function testHasRequestedCountsOnlyThatLoginAndPlugin(): void
    {
        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('OtherPlugin', 'bob', strtotime('2026-10-01 10:00:00'));

        self::assertTrue($this->history->hasRequested('PremiumPlugin', 'alice'));
        self::assertFalse($this->history->hasRequested('PremiumPlugin', 'bob'));
        self::assertFalse($this->history->hasRequested('OtherPlugin', 'alice'));
    }

    public function testHasRequestedSeesRequestsAddedAfterAnEarlierCheck(): void
    {
        self::assertFalse($this->history->hasRequested('PremiumPlugin', 'alice'));

        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        self::assertTrue($this->history->hasRequested('PremiumPlugin', 'alice'));

        $this->history->anonymizeLogin('alice');
        self::assertFalse($this->history->hasRequested('PremiumPlugin', 'alice'));
    }

    public function testEachLoginIsRecordedOncePerPlugin(): void
    {
        self::assertTrue($this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00')));
        self::assertFalse($this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-02 10:00:00')));
        // a later request that only the option holds, as Matomo before the update can store
        $this->history->addIfMissing('PremiumPlugin', 'alice', strtotime('2026-10-03 10:00:00'));

        self::assertSame(
            ['2026-10-01 10:00:00'],
            array_column($this->history->getRequests('PremiumPlugin'), 'ts_requested')
        );
    }

    public function testDeletingUserKeepsTheirRequestsWithoutTheirLogin(): void
    {
        Fixture::createSuperUser();
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('bob', 'secret-password-2', 'bob@example.com');

        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('PremiumPlugin', 'bob', strtotime('2026-10-01 10:00:00'));

        UsersManagerAPI::getInstance()->deleteUser('alice');

        self::assertSame(
            ['bob', null],
            array_column($this->history->getRequests('PremiumPlugin'), 'login')
        );
    }

    public function testLoginsAreMatchedWhateverTheirCase(): void
    {
        Fixture::createSuperUser();
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        $this->history->add('PremiumPlugin', 'Alice', strtotime('2026-10-01 10:00:00'));

        self::assertSame('alice@example.com', $this->history->getPendingRequests()[0]['email']);
        self::assertSame([], $this->history->getDeletedLogins());
    }

    public function testLoginsAreMatchedAgainstAUserTableWithAnotherCollation(): void
    {
        Fixture::createSuperUser();
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('PremiumPlugin', 'carol', strtotime('2026-10-02 10:00:00'));

        $restore = $this->giveTheLoginColumnsAnotherCollationThanTheUserTable();
        try {
            self::assertSame(
                [['carol', null], ['alice', 'alice@example.com']],
                array_map(function (array $request): array {
                    return [$request['login'], $request['email']];
                }, $this->history->getPendingRequests())
            );
            self::assertSame(['carol'], $this->history->getDeletedLogins());

            $decision = $this->history->addDecision('PremiumPlugin', RequestHistory::DECISION_APPROVED, 'dave', null, time());
            self::assertNotNull($decision);
            self::assertSame(['alice' => 'alice@example.com'], $this->history->getRequesterEmails($decision));
            self::assertSame(['carol', 'dave'], $this->history->getDeletedLogins());
        } finally {
            $restore();
        }
    }

    /**
     * Like an instance whose user table was created long before these, under another default collation
     *
     * @return callable restores the columns
     */
    private function giveTheLoginColumnsAnotherCollationThanTheUserTable(): callable
    {
        $userLogin = Db::fetchRow(
            'SELECT CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [Common::prefixTable('user'), 'login']
        );
        $charset = $userLogin['CHARACTER_SET_NAME'];
        // a _bin collation would take precedence in a comparison instead of failing it
        $collation = $userLogin['COLLATION_NAME'] === $charset . '_unicode_ci' ? $charset . '_general_ci' : $charset . '_unicode_ci';

        $restores = [];
        foreach ([RequestHistory::TABLE_NAME => 'login', RequestHistory::DECISION_TABLE_NAME => 'decided_by'] as $table => $column) {
            $table = Common::prefixTable($table);
            $definition = Db::fetchRow(
                'SELECT COLUMN_TYPE, IS_NULLABLE, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS'
                . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$table, $column]
            );
            $alter = function (string $charset, string $collation) use ($table, $column, $definition): void {
                Db::exec(sprintf(
                    'ALTER TABLE `%s` MODIFY `%s` %s CHARACTER SET %s COLLATE %s %s',
                    $table,
                    $column,
                    $definition['COLUMN_TYPE'],
                    $charset,
                    $collation,
                    $definition['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL'
                ));
            };
            $alter($charset, $collation);
            $restores[] = function () use ($alter, $definition): void {
                $alter($definition['CHARACTER_SET_NAME'], $definition['COLLATION_NAME']);
            };
        }

        return function () use ($restores): void {
            foreach ($restores as $restore) {
                $restore();
            }
        };
    }
}
