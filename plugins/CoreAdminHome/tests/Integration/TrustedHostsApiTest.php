<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Access;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreAdminHome\API;
use Piwik\Request\AuthenticationToken;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreAdminHome
 * @group TrustedHostsApiTest
 * @group API
 * @group Plugins
 */
class TrustedHostsApiTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createSuperUser(false);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();

        Access::getInstance()->setSuperUserAccess(false);
        $auth = StaticContainer::get(Auth::class);
        $auth->setLogin(Fixture::ADMIN_USER_LOGIN);
        $auth->setPassword(Fixture::ADMIN_USER_PASSWORD);
        Access::getInstance()->reloadAccess($auth);
    }

    public function tearDown(): void
    {
        $this->useTokenAuth();

        parent::tearDown();
    }

    public function testSetTrustedHostsThrowsIfNoPasswordConfirmationGivenForSessionAuth()
    {
        $this->useSessionAuth();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithReAuthentication');

        $this->api->setTrustedHosts(['example.org']);
    }

    public function testSetTrustedHostsThrowsIfPasswordConfirmationWrongForSessionAuth()
    {
        $this->useSessionAuth();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_CurrentPasswordNotCorrect');

        $this->api->setTrustedHosts(['example.org'], 'wrongpassword');
    }

    public function testSetTrustedHostsAcceptsCorrectPasswordConfirmationForSessionAuth()
    {
        $this->useSessionAuth();

        // an empty list of hosts is not saved, so this only verifies the password confirmation passes
        self::assertTrue($this->api->setTrustedHosts([], Fixture::ADMIN_USER_PASSWORD));
    }

    public function testSetTrustedHostsDoesNotRequirePasswordConfirmationForTokenAuth()
    {
        $this->useTokenAuth();

        self::assertTrue($this->api->setTrustedHosts([]));
    }

    private function useSessionAuth(): void
    {
        $_POST['token_auth'] = Fixture::getTokenAuth();
        $_POST['force_api_session'] = 1;
        StaticContainer::getContainer()->set(AuthenticationToken::class, new AuthenticationToken());
    }

    private function useTokenAuth(): void
    {
        unset($_POST['token_auth'], $_POST['force_api_session']);
        StaticContainer::getContainer()->set(AuthenticationToken::class, new AuthenticationToken());
    }
}
