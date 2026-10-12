<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Access;
use Piwik\AuthResult;
use Piwik\Date;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Listing a login's own sites reads the access rows directly rather than the lists the token's access
 * level is applied to, so it is the one site-listing path that could answer past that access level.
 *
 * @group Plugins
 * @group SitesManager
 */
class ScopedTokenSiteListingTest extends IntegrationTestCase
{
    private const LOGIN = 'scopedsitelisting';

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $model = new UsersModel();
        $model->addUser(self::LOGIN, 'pwhash', 'scopedsitelisting@example.org', Date::now()->getDatetime());
        $model->addUserAccess(self::LOGIN, Access\Role\Admin::ID, [$this->idSite]);
    }

    public function testListingOwnSitesReturnsNothingWhenTheTokenScopeCouldNotBeEstablished()
    {
        $this->authenticateWithTokenAccessLevel('notalevel');

        $this->assertSame([], API::getInstance()->getSitesIdWithAtLeastViewAccess(self::LOGIN));
        $this->assertSame([], API::getInstance()->getSitesWithAtLeastViewAccess(false, self::LOGIN));
    }

    public function testListingOwnSitesStillAnswersForATokenScopedBelowTheUsersRole()
    {
        $this->authenticateWithTokenAccessLevel('view');

        $this->assertSame([$this->idSite], API::getInstance()->getSitesIdWithAtLeastViewAccess(self::LOGIN));
    }

    private function authenticateWithTokenAccessLevel(string $accessLevel): void
    {
        $authMock = $this->getMockBuilder('Piwik\Auth')
            ->onlyMethods([
                'authenticate',
                'getName',
                'getTokenAuthSecret',
                'getLogin',
                'setTokenAuth',
                'setLogin',
                'setPassword',
                'setPasswordHash',
            ])
            ->getMock();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->willReturn(new AuthResult(
                AuthResult::SUCCESS,
                self::LOGIN,
                'token',
                ['token_access_level' => $accessLevel]
            ));

        $access = Access::getInstance();
        $access->setSuperUserAccess(false);
        $this->assertTrue($access->reloadAccess($authMock));
    }
}
