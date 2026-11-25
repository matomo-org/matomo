<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Db;
use Piwik\Plugins\Marketplace\PluginTrial\UnsubscribeToken;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class UnsubscribeTokenTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new UsersModel();
        $model->addUser('alice', 'hash', 'alice@example.com', '2026-01-01 00:00:00');
        $model->addUser('bob', 'hash', 'bob@example.com', '2026-01-01 00:00:00');
    }

    public function testTokenIsValidOnlyForTheLoginItWasGeneratedFor()
    {
        $token = new UnsubscribeToken();

        self::assertTrue($token->isValid('alice', $token->generate('alice')));
        self::assertFalse($token->isValid('bob', $token->generate('alice')));
        self::assertFalse($token->isValid('alice', ''));
        self::assertFalse($token->isValid('', ''));
    }

    public function testTokenIsInvalidForAnUnknownLogin()
    {
        $token = new UnsubscribeToken();

        self::assertNull($token->generate('carol'));
        self::assertFalse($token->isValid('carol', ''));
    }

    public function testTokenStopsWorkingWhenTheLoginIsRecreated()
    {
        $token = new UnsubscribeToken();
        $oldToken = $token->generate('alice');

        $model = new UsersModel();
        $model->deleteUserOnly('alice');
        $model->addUser('alice', 'hash', 'alice@example.com', '2026-06-01 00:00:00');

        self::assertFalse($token->isValid('alice', $oldToken));
        self::assertTrue($token->isValid('alice', $token->generate('alice')));
    }

    public function testTokenSurvivesADatabaseTimeZoneChange()
    {
        $token = new UnsubscribeToken();
        $timeZone = Db::fetchOne('SELECT @@session.time_zone');

        Db::exec("SET time_zone = '+00:00'");
        try {
            $issued = $token->generate('alice');
            Db::exec("SET time_zone = '+05:00'");
            self::assertTrue($token->isValid('alice', $issued));
        } finally {
            Db::query('SET time_zone = ?', [$timeZone]);
        }
    }

    public function testTokenWorksForAUserWithoutARegistrationDate()
    {
        (new UsersModel())->updateUserFields('alice', ['date_registered' => null]);
        $token = new UnsubscribeToken();

        self::assertTrue($token->isValid('alice', $token->generate('alice')));
    }
}
