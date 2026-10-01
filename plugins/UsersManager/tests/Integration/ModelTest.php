<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Access\Role\View;
use Piwik\Access\Role\Write;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\EventDispatcher;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Session\SessionFingerprint;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group APITest
 * @group Plugins
 */
class ModelTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var Model
     */
    private $model;

    private $login = 'userLogin';
    private $login2 = 'userLogin2';
    private $login3 = 'pendingLogin3';

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();
        $this->model = new Model();

        FakeAccess::clearAccess();
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        $this->api->addUser($this->login, 'password', 'userlogin@password.de');
        $this->api->addUser($this->login2, 'password2', 'userlogin2@password.de');
    }

    public function testGetSitesAccessFromUserNoAccess()
    {
        $this->assertSame(array(), $this->model->getSitesAccessFromUser($this->login));
    }

    public function testGetSitesAccessFromUserAccessOneSite()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->assertEquals(array(
            array('site' => '2', 'access' => Write::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function testGetSitesAccessFromUserMultipleSites()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        $access = $this->model->getSitesAccessFromUser($this->login);
        // The order might differ depending on the database, so sort by 'site'
        usort($access, function ($a, $b) {
            return $a['site'] - $b['site'];
        });
        $this->assertEquals(array(
            array('site' => '1', 'access' => View::ID),
            array('site' => '2', 'access' => Write::ID),
            array('site' => '3', 'access' => Write::ID),
        ), $access);
    }

    public function testGetSitesAccessFromUserMultipleSitesSomeNoLongerExist()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        SitesManagerAPI::getInstance()->deleteSite(2);
        SitesManagerAPI::getInstance()->deleteSite(1);
        $this->assertEquals(array(
            array('site' => '3', 'access' => Write::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function testGetSitesAccessFromUserSiteDeletedManually()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        Db::query('DELETE FROM ' . Common::prefixTable('site') . ' where idsite = 1');
        Db::query('DELETE FROM ' . Common::prefixTable('site') . ' where idsite = 2');
        $this->assertEquals(array(
            array('site' => '3', 'access' => Write::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function testGetAllNonSystemTokensForLoginWhenNoTokenConfigured()
    {
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertSame(array(), $tokens);
    }

    public function testAddTokenAuthMinimal()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05');
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals(array(array(
            'idusertokenauth' => '1',
            'login' => 'userLogin',
            'description' => 'MyDescription',
            'password' => '2265daba0872fc3aef169d079365e590f0cbc8ed46c2a7984c8a642803cfd96cb47804a63cf22a79f6ca469268c29ee9e72a5059b62d0a598fe42dfc8dcc51bc',
            'hash_algo' => 'sha512',
            'system_token' => '0',
            'last_used' => null,
            'date_created' => '2020-01-02 03:04:05',
            'date_expired' => null,
            'secure_only' => '0',
            'ts_rotation_notified' => null,
            'ts_expiration_warning_notified' => null,
        )), $tokens);
    }

    public function testAddTokenAuthExpire()
    {
        $id = $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', '2030-01-05 03:04:05');
        $this->assertEquals(1, $id);
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals(array(array(
            'idusertokenauth' => '1',
            'login' => 'userLogin',
            'description' => 'MyDescription',
            'password' => '2265daba0872fc3aef169d079365e590f0cbc8ed46c2a7984c8a642803cfd96cb47804a63cf22a79f6ca469268c29ee9e72a5059b62d0a598fe42dfc8dcc51bc',
            'hash_algo' => 'sha512',
            'system_token' => '0',
            'last_used' => null,
            'date_created' => '2020-01-02 03:04:05',
            'date_expired' => '2030-01-05 03:04:05',
            'secure_only' => '0',
            'ts_rotation_notified' => null,
            'ts_expiration_warning_notified' => null,
        )), $tokens);
    }

    public function testAddTokenAuthThrowsExceptionIfUserNotExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('does not exist');
        $this->model->addTokenAuth('foobar', 'token', 'MyDescription', '2020-01-02 03:04:05', '2030-01-05 03:04:05');
    }

    public function testAddTokenAuthThrowsExceptionFailsAddingSameTwice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate entry');
        $this->model->addTokenAuth($this->login, 'token', 'My description', '2020-01-02 03:04:05');
        $this->model->addTokenAuth($this->login, 'token', 'My duplicate', '2020-01-03 03:04:05');
    }

    public function testAddTokenAuthReturnsId()
    {
        $id = $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05');
        $this->assertEquals(1, $id);
        $id = $this->model->addTokenAuth($this->login, 'token2', 'MyDescription', '2020-01-02 03:04:05');
        $this->assertEquals(2, $id);
    }

    public function testAddTokenAuthThrowsExceptionNoDescription()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorEmptyValue');
        $this->model->addTokenAuth($this->login, 'token', '', '2020-01-02 03:04:05');
    }

    public function testGetAllNonSystemTokensForLoginDoesNotReturnSystemTokens()
    {
        $this->model->addTokenAuth($this->login, 'token2', 'api usage token', '2020-01-02 03:04:05', null, true);
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals(array(), $tokens);
    }

    public function testGetAllNonSystemTokensForLoginDoesNotReturnExpiredTokens()
    {
        $this->model->addTokenAuth($this->login, 'token2', 'api usage token', '2020-01-02 03:04:05', '2019-01-05 03:04:05');
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals(array(), $tokens);
    }

    public function testGetAllNonSystemTokensForLoginReturnsNotExpiredToken()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', '2030-01-05 03:04:05');
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals(array(array(
            'idusertokenauth' => '1',
            'login' => 'userLogin',
            'description' => 'MyDescription',
            'password' => '2265daba0872fc3aef169d079365e590f0cbc8ed46c2a7984c8a642803cfd96cb47804a63cf22a79f6ca469268c29ee9e72a5059b62d0a598fe42dfc8dcc51bc',
            'hash_algo' => 'sha512',
            'system_token' => '0',
            'last_used' => null,
            'date_created' => '2020-01-02 03:04:05',
            'date_expired' => '2030-01-05 03:04:05',
            'secure_only' => '0',
            'ts_rotation_notified' => null,
            'ts_expiration_warning_notified' => null,
        )), $tokens);
    }

    public function testGetUserByTokenAuthFindsUserWhenTokenNotYetExpired()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', '2030-01-05 03:04:05');
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertSame($this->login, $user['login']);
    }

    public function testGetUserByTokenAuthFindsUserWhenNoExpireDateSet()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05');
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertSame($this->login, $user['login']);
    }

    public function testGetUserByTokenAuthNotFindsUserWhenTokenIsExpired()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', '2019-03-04 00:05:06');
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertEmpty($user);
    }

    public function testGetUserByTokenAuthFindsUserWhenTokenIsSystemToken()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', null, true);
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertSame($this->login, $user['login']);
    }

    public function testGenerateRandomTokenAuthCorrectFormat()
    {
        $token = $this->model->generateRandomTokenAuth();
        $this->assertSame(32, strlen($token));
        $this->assertTrue(ctype_xdigit($token));
    }

    public function testGenerateRandomTokenAuthIsAlwaysDifferent()
    {
        $this->assertNotEquals($this->model->generateRandomTokenAuth(), $this->model->generateRandomTokenAuth());
    }

    public function testHashTokenAuth()
    {
        $this->assertSame('2265daba0872fc3aef169d079365e590f0cbc8ed46c2a7984c8a642803cfd96cb47804a63cf22a79f6ca469268c29ee9e72a5059b62d0a598fe42dfc8dcc51bc', $this->model->hashTokenAuth('token'));
        $this->assertSame('02c2e43dcb393097a1221465812a4e9b1e1e80f16e92b313fd4ce8c5ee5b8272a17cd8cdc1ce63578494eaba739c6f7abba7890506ef6bf8d607538778f2a849', $this->model->hashTokenAuth('token2'));
    }

    public function testGetAllHashedTokensForLoginsNoLoginsSet()
    {
        $this->assertSame(array(), $this->model->getAllHashedTokensForLogins(array()));
    }

    public function testGetAllHashedTokensForLoginsNoTokensExist()
    {
        $this->assertSame(array(), $this->model->getAllHashedTokensForLogins(array('foo', 'bar')));
    }

    public function testGetAllHashedTokensForLogins()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', null, true);
        $this->model->addTokenAuth($this->login, 'token2', 'MyDescription', '2020-01-02 03:04:05', null, false);
        // does not return expired tokens
        $this->model->addTokenAuth($this->login, 'token3', 'MyDescription', '2020-01-02 03:04:05', '2019-02-03 00:01:02', true);

        $this->assertSame(array(), $this->model->getAllHashedTokensForLogins(array('foo', 'bar')));

        $this->assertSame(array(
            '2265daba0872fc3aef169d079365e590f0cbc8ed46c2a7984c8a642803cfd96cb47804a63cf22a79f6ca469268c29ee9e72a5059b62d0a598fe42dfc8dcc51bc',
            '02c2e43dcb393097a1221465812a4e9b1e1e80f16e92b313fd4ce8c5ee5b8272a17cd8cdc1ce63578494eaba739c6f7abba7890506ef6bf8d607538778f2a849',
        ), $this->model->getAllHashedTokensForLogins(array('foo', $this->login, 'bar')));
    }

    public function testDeleteToken()
    {
        $id1 = $this->model->addTokenAuth($this->login, 'token', 'MyDescription1', '2020-01-02 03:04:05');
        $id2 = $this->model->addTokenAuth($this->login, 'token2', 'MyDescription2', '2020-01-03 03:04:05');

        // should not have deleted anything as it doesn't match
        $this->model->deleteToken(999, $this->login);
        $this->model->deleteToken($id1, 'foobar');

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertCount(2, $tokens);
        $this->assertEquals($id1, $tokens[0]['idusertokenauth']);
        $this->assertEquals($id2, $tokens[1]['idusertokenauth']);

        // should only delete that id
        $this->model->deleteToken($id1, $this->login);

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertCount(1, $tokens);
        $this->assertEquals($id2, $tokens[0]['idusertokenauth']);
    }

    public function testDeleteAllTokensForUser()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription1', '2020-01-02 03:04:05');
        $this->model->addTokenAuth($this->login, 'token2', 'MyDescription2', '2020-01-03 03:04:05');
        $this->model->addTokenAuth($this->login2, 'token3', 'MyDescription2', '2020-01-03 03:04:05');

        // should not have deleted anything as it doesn't match
        $this->model->deleteAllTokensForUser('foobar');

        $this->assertCount(2, $this->model->getAllNonSystemTokensForLogin($this->login));
        $this->assertCount(1, $this->model->getAllNonSystemTokensForLogin($this->login2));

        // should only delete tokens for that login
        $this->model->deleteAllTokensForUser($this->login);

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertCount(0, $this->model->getAllNonSystemTokensForLogin($this->login));
        $this->assertCount(1, $this->model->getAllNonSystemTokensForLogin($this->login2));
    }

    public function testSetTokenAuthWasUsed()
    {
        $this->model->addTokenAuth($this->login, 'token2', 'MyDescription', '2020-01-02 03:04:05');
        $this->model->setTokenAuthWasUsed('token2', '2025-01-02 03:04:05');

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertSame('2025-01-02 03:04:05', $tokens[0]['last_used']);

        // this should not update the token usage again, as it's within 10 minutes
        $this->model->setTokenAuthWasUsed('token2', '2025-01-02 03:08:05');

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertSame('2025-01-02 03:04:05', $tokens[0]['last_used']);

        // this should update the token usage again, as it's after 10 minutes
        $this->model->setTokenAuthWasUsed('token2', '2025-01-02 03:15:05');

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertSame('2025-01-02 03:15:05', $tokens[0]['last_used']);
    }

    public function testSetTokenAuthWasUsedDoesNotFailWhenTokenNotExists()
    {
        $this->expectNotToPerformAssertions();
        $this->model->setTokenAuthWasUsed('tokenFooBar', '2025-01-02 03:04:05');
    }

    public function testDeleteUserSessionsDeletesMatchingSessionsOnly()
    {
        $this->insertSessionRowForLogin('login2character', '');
        $this->insertSessionRowForLogin('login22character', '');
        $this->insertSessionRowForLogin('login222character', '');
        $this->insertSessionRowForLogin('login2222character', '');

        $variations = ['login1character', 'login11character', 'login111character', 'login1111character', '用户'];

        foreach ($variations as $variation) {
            $sessionTable = Common::prefixTable('session');

            // randomise position of username in the session string
            $this->insertSessionRowForLogin($variation, '');
            $this->insertSessionRowForLogin($variation, '1');
            $this->insertSessionRowForLogin($variation, '11');
            $this->insertSessionRowForLogin($variation, '111');

            $countBefore = (int) Db::fetchOne('SELECT COUNT(*) FROM `' . $sessionTable . '`');
            $this->assertSame(8, $countBefore);

            $this->model->deleteUserSessions($variation);

            $countAfter = (int) Db::fetchOne('SELECT COUNT(*) FROM `' . $sessionTable . '`');
            $this->assertSame(4, $countAfter, $variation . ' test failed');

            // check won't delete not matching username
            $this->model->deleteUserSessions('notExistingUsername');
            $this->assertSame(4, $countAfter, $variation . ' test failed when nothing should be deleted');
        }
    }

    public function testDeleteUserSessionsHandlesLoginContainingUnderscore()
    {
        $sessionTable = Common::prefixTable('session');
        $underscoreLogin = 'user_name_1';
        $this->api->addUser($underscoreLogin, 'password3', 'username1@password.de');

        $this->insertSessionRowForLogin($underscoreLogin, 'bar');
        $this->insertSessionRowForLogin($this->login2, 'foo');

        $this->model->deleteUserSessions($underscoreLogin);

        $remainingRows = (int) Db::fetchOne('SELECT COUNT(*) FROM `' . $sessionTable . '`');
        $this->assertSame(1, $remainingRows);
    }

    public function testDeleteExpiredTokens()
    {
        $date = Date::factory('now')->addMonth(1)->getDatetime();
        $dateNotExpired = Date::factory('now')->addMonth(24)->getDatetime();
        $dateExpired =  Date::factory('now')->subMonth(1)->getDatetime();

        $id1 = $this->model->addTokenAuth($this->login, 'token', 'MyDescription1', '2020-01-01 03:04:05', $dateExpired);
        $id2 = $this->model->addTokenAuth($this->login, 'token2', 'MyDescription2', '2020-01-02 03:04:05');
        $id3 = $this->model->addTokenAuth($this->login, 'token3', 'MyDescription3', '2020-01-03 03:04:05', $dateNotExpired);
        $id4 = $this->model->addTokenAuth($this->login2, 'token4', 'MyDescription4', '2020-01-04 03:04:05', $dateNotExpired);
        $id5 = $this->model->addTokenAuth($this->login2, 'token5', 'MyDescription5', '2020-01-05 03:04:05');
        $id6 = $this->model->addTokenAuth($this->login2, 'token6', 'MyDescription6', '2020-01-06 03:04:05', '2018-01-02 03:04:05');

        // id1 and id6 are expired and should have been deleted
        $this->model->deleteExpiredTokens($date);

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals($id2, $tokens[0]['idusertokenauth']);
        $this->assertEquals($id3, $tokens[1]['idusertokenauth']);
        $this->assertCount(2, $tokens);

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login2);
        $this->assertEquals($id4, $tokens[0]['idusertokenauth']);
        $this->assertEquals($id5, $tokens[1]['idusertokenauth']);
        $this->assertCount(2, $tokens);
    }

    public function testAddUserAccessRefusesALoginWithoutAUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        try {
            $this->model->addUserAccess('noSuchLogin', View::ID, array(1));
        } finally {
            $this->assertSame(0, $this->countAccessRows('noSuchLogin'));
        }
    }

    public function testAddUserAccessStillGrantsAccessToAnExistingUser()
    {
        $this->model->addUserAccess($this->login, View::ID, array(1));

        $this->assertEquals(array(
            array('site' => '1', 'access' => View::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function testAddUserRemovesAccessAndTokensLeftBehindByAnEarlierAccount()
    {
        $recycledLogin = 'recycledLogin';

        $this->insertAccessRow($recycledLogin, 1, View::ID);
        $this->insertTokenRow($recycledLogin);

        $this->assertSame(1, $this->countAccessRows($recycledLogin));
        $this->assertCount(1, $this->model->getAllNonSystemTokensForLogin($recycledLogin));

        // added through the model directly, the way just in time provisioning creates users
        $this->model->addUser($recycledLogin, 'password', 'recycled@example.org', Date::now()->getDatetime());

        $this->assertSame(0, $this->countAccessRows($recycledLogin));
        $this->assertSame(array(), $this->model->getSitesAccessFromUser($recycledLogin));
        $this->assertSame(array(), $this->model->getAllNonSystemTokensForLogin($recycledLogin));
    }

    public function testAddUserKeepsTheAccessOfTheAnonymousUser()
    {
        // the anonymous user is created during installation, so it can already be present here
        Db::query('DELETE FROM ' . Common::prefixTable('user') . ' WHERE login = ?', array('anonymous'));

        $this->insertAccessRow('anonymous', 1, View::ID);
        $this->assertSame(1, $this->countAccessRows('anonymous'));

        $this->model->addUser('anonymous', '', 'anonymous@example.org', Date::now()->getDatetime());

        $this->assertSame(1, $this->countAccessRows('anonymous'));
    }

    public function testAddUserAnnouncesTheCreatedLoginOnce()
    {
        $observedLogins = array();

        EventDispatcher::getInstance()->addObserver(
            'UsersManager.createUser',
            function ($userLogin) use (&$observedLogins) {
                $observedLogins[] = $userLogin;
            }
        );

        $this->model->addUser('createdLogin', 'password', 'created@example.org', Date::now()->getDatetime());

        $this->assertSame(array('createdLogin'), $observedLogins);
    }

    public function testAddTokenAuthRefusesARegistrationDateThatDoesNotMatchTheAccount()
    {
        $otherDateRegistered = Date::factory($this->getDateRegistered($this->login))->addDay(1)->getDatetime();

        $caught = null;

        try {
            $this->model->addTokenAuth(
                $this->login,
                'token',
                'MyDescription',
                Date::now()->getDatetime(),
                null,
                false,
                false,
                $otherDateRegistered
            );
        } catch (\Exception $e) {
            $caught = $e;
        }

        $this->assertNotNull($caught, 'A registration date that does not match should not store a token');
        $this->assertSame(array(), $this->model->getAllNonSystemTokensForLogin($this->login));
    }

    public function testAddTokenAuthStoresTheTokenWhenTheRegistrationDateMatchesTheAccount()
    {
        $idToken = $this->model->addTokenAuth(
            $this->login,
            'token',
            'MyDescription',
            Date::now()->getDatetime(),
            null,
            false,
            false,
            $this->getDateRegistered($this->login)
        );

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);

        $this->assertCount(1, $tokens);
        $this->assertEquals($idToken, $tokens[0]['idusertokenauth']);
    }

    public function testAddTokenAuthStoresTheTokenWhenNoRegistrationDateIsGiven()
    {
        $idToken = $this->model->addTokenAuth($this->login, 'token', 'MyDescription', Date::now()->getDatetime());

        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);

        $this->assertCount(1, $tokens);
        $this->assertEquals($idToken, $tokens[0]['idusertokenauth']);
    }

    public function testAddUserWritesTheInvitationWithTheAccount()
    {
        $this->model->addUser($this->login3, '', 'pending@pending.de', Date::now()->getDatetime(), [
            'token'        => 'inviteToken',
            'expiryInDays' => 7,
            'invitedBy'    => $this->login,
        ]);

        $stored = $this->model->getUser($this->login3);
        self::assertSame($this->model->hashTokenAuth('inviteToken'), $stored['invite_token']);
        self::assertSame($this->login, $stored['invited_by']);
        self::assertSame(
            Date::now()->addDay(7)->toString('Y-m-d'),
            Date::factory($stored['invite_expired_at'])->toString('Y-m-d')
        );
    }

    public function testAddUserLeavesTheInvitationFieldsEmptyWhenNoneIsGiven()
    {
        $this->model->addUser($this->login3, '', 'pending@pending.de', Date::now()->getDatetime());

        $stored = $this->model->getUser($this->login3);
        self::assertNull($stored['invite_token']);
        self::assertNull($stored['invite_expired_at']);
        self::assertNull($stored['invited_by']);
    }

    public function testAttachInviteLinkTokenAttachesLinkToPendingUser()
    {
        $user = $this->createPendingUser();

        self::assertTrue(
            $this->model->attachInviteLinkToken($this->login3, 'linkToken', $user['invite_token'], 7)
        );

        $stored = $this->model->getUser($this->login3);
        self::assertSame($this->model->hashTokenAuth('linkToken'), $stored['invite_link_token']);
        // the token mailed to the invitee keeps working alongside the link
        self::assertSame($user['invite_token'], $stored['invite_token']);
    }

    public function testAttachInviteLinkTokenRefusesOnceTheInvitationWasAccepted()
    {
        $user = $this->createPendingUser();
        $this->model->consumeInviteToken($this->login3, 'inviteToken', 'hashedPassword');

        self::assertFalse(
            $this->model->attachInviteLinkToken($this->login3, 'linkToken', $user['invite_token'], 7)
        );

        $stored = $this->model->getUser($this->login3);
        self::assertNull($stored['invite_link_token']);
        self::assertNull($stored['invite_expired_at']);
    }

    public function testAttachInviteLinkTokenRefusesWhenTheInviteTokenWasRotated()
    {
        $user = $this->createPendingUser();
        $this->model->attachInviteToken($this->login3, 'rotatedToken', 7);

        self::assertFalse(
            $this->model->attachInviteLinkToken($this->login3, 'linkToken', $user['invite_token'], 7)
        );
        self::assertNull($this->model->getUser($this->login3)['invite_link_token']);
    }

    public function testReissueInviteTokenForPendingUserRotatesTheTokenAndMovesTheAddress()
    {
        $user = $this->createPendingUser();

        self::assertTrue($this->model->reissueInviteTokenForPendingUser(
            $this->login3,
            'reissuedToken',
            $user['invite_token'],
            $user['email'],
            'moved@pending.de',
            7
        ));

        $stored = $this->model->getUser($this->login3);
        self::assertSame($this->model->hashTokenAuth('reissuedToken'), $stored['invite_token']);
        self::assertSame('moved@pending.de', $stored['email']);
        // the address moved and the previous token stopped working in the same statement
        self::assertEmpty($this->model->getUserByInviteToken('inviteToken'));
    }

    public function testReissueInviteTokenForPendingUserRefusesWhenTheAddressAlreadyChanged()
    {
        $user = $this->createPendingUser();
        $this->model->updateUser($this->login3, false, 'someone.else@pending.de');

        self::assertFalse($this->model->reissueInviteTokenForPendingUser(
            $this->login3,
            'reissuedToken',
            $user['invite_token'],
            $user['email'],
            'moved@pending.de',
            7
        ));

        $stored = $this->model->getUser($this->login3);
        self::assertSame('someone.else@pending.de', $stored['email']);
        self::assertSame($user['invite_token'], $stored['invite_token']);
    }

    public function testReissueInviteTokenForPendingUserRenewsALapsedInvitation()
    {
        $user = $this->createPendingUser();
        $this->expireInvitation();

        self::assertTrue($this->model->reissueInviteTokenForPendingUser(
            $this->login3,
            'reissuedToken',
            $user['invite_token'],
            $user['email'],
            $user['email'],
            7
        ));
    }

    public function testConsumeInviteTokenActivatesTheAccount()
    {
        $this->createPendingUser();

        self::assertTrue($this->model->consumeInviteToken($this->login3, 'inviteToken', 'hashedPassword'));

        $stored = $this->model->getUser($this->login3);
        self::assertSame('hashedPassword', $stored['password']);
        self::assertNull($stored['invite_token']);
        self::assertNull($stored['invite_link_token']);
        self::assertNull($stored['invite_expired_at']);
        self::assertNotEmpty($stored['invite_accept_at']);
        // bypassing updateUserFields() must not lose the password change timestamp
        self::assertNotEmpty($stored['ts_password_modified']);
    }

    public function testConsumeInviteTokenAcceptsTheCopiedLinkToken()
    {
        $user = $this->createPendingUser();
        $this->model->attachInviteLinkToken($this->login3, 'linkToken', $user['invite_token'], 7);

        self::assertTrue($this->model->consumeInviteToken($this->login3, 'linkToken', 'hashedPassword'));
    }

    public function testConsumeInviteTokenAppliesOnlyOnce()
    {
        $user = $this->createPendingUser();
        $this->model->attachInviteLinkToken($this->login3, 'linkToken', $user['invite_token'], 7);

        // a pending account can carry both the mailed token and the copied link
        self::assertTrue($this->model->consumeInviteToken($this->login3, 'inviteToken', 'invitee'));
        self::assertFalse($this->model->consumeInviteToken($this->login3, 'linkToken', 'inviter'));

        self::assertSame('invitee', $this->model->getUser($this->login3)['password']);
    }

    public function testConsumeInviteTokenRefusesAnExpiredInvitation()
    {
        $this->createPendingUser();
        $this->expireInvitation();

        self::assertFalse($this->model->consumeInviteToken($this->login3, 'inviteToken', 'hashedPassword'));
        self::assertEmpty($this->model->getUser($this->login3)['password']);
    }

    public function testConsumeInviteTokenRefusesATokenLeftOnAnActiveAccount()
    {
        // an active account that still carries a link token from an earlier invitation
        $this->model->updateUserFields($this->login, [
            'invite_link_token' => $this->model->hashTokenAuth('orphanToken'),
            'invite_expired_at' => Date::now()->addDay(7)->getDatetime(),
        ]);

        self::assertFalse($this->model->consumeInviteToken($this->login, 'orphanToken', 'hashedPassword'));
        self::assertNotSame('hashedPassword', $this->model->getUser($this->login)['password']);
    }

    public function testGetUserByInviteTokenIgnoresATokenLeftOnAnActiveAccount()
    {
        $this->createPendingUser();
        self::assertNotEmpty($this->model->getUserByInviteToken('inviteToken'));

        $this->model->consumeInviteToken($this->login3, 'inviteToken', 'hashedPassword');

        // an invitation link left behind on an account that is no longer pending resolves to nothing,
        // however it got there
        $this->model->updateUserFields($this->login3, [
            'invite_link_token' => $this->model->hashTokenAuth('orphanedLinkToken'),
            'invite_expired_at' => Date::now()->addDay(1)->getDatetime(),
        ]);

        self::assertEmpty($this->model->getUserByInviteToken('inviteToken'));
        self::assertEmpty($this->model->getUserByInviteToken('orphanedLinkToken'));
    }

    public function testGenerateRandomInviteTokenStillSeesTokensOnNonPendingAccounts()
    {
        // token uniqueness has to stay global, so a token parked on an active account still collides
        $this->model->updateUserFields($this->login, [
            'invite_link_token' => $this->model->hashTokenAuth('orphanToken'),
        ]);

        self::assertTrue($this->invokeInviteTokenExists('orphanToken'));
        self::assertFalse($this->invokeInviteTokenExists('neverIssuedToken'));
    }

    public function testDeletePendingUserByInviteTokenRemovesTheAccount()
    {
        $this->createPendingUser();

        self::assertTrue($this->model->deletePendingUserByInviteToken($this->login3, 'inviteToken'));
        self::assertEmpty($this->model->getUser($this->login3));
    }

    public function testDeletePendingUserByInviteTokenAppliesOnlyOnce()
    {
        $user = $this->createPendingUser();
        $this->model->attachInviteLinkToken($this->login3, 'linkToken', $user['invite_token'], 7);

        self::assertTrue($this->model->deletePendingUserByInviteToken($this->login3, 'inviteToken'));
        self::assertFalse($this->model->deletePendingUserByInviteToken($this->login3, 'linkToken'));
    }

    public function testDeletePendingUserByInviteTokenRefusesOnceTheInvitationWasAccepted()
    {
        $this->createPendingUser();
        $this->model->consumeInviteToken($this->login3, 'inviteToken', 'hashedPassword');

        self::assertFalse($this->model->deletePendingUserByInviteToken($this->login3, 'inviteToken'));
        self::assertNotEmpty($this->model->getUser($this->login3));
    }

    public function testDeletePendingUserByInviteTokenToleratesAnInvitationWithoutExpiry()
    {
        $this->createPendingUser();
        $this->model->updateUserFields($this->login3, ['invite_expired_at' => null]);

        // decline has always been more lenient about the expiry than acceptance is
        self::assertTrue($this->model->deletePendingUserByInviteToken($this->login3, 'inviteToken'));
    }

    private function getDateRegistered(string $login): string
    {
        return (string) Db::fetchOne(
            'SELECT date_registered FROM ' . Common::prefixTable('user') . ' WHERE login = ?',
            array($login)
        );
    }

    private function countAccessRows(string $login): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('access') . ' WHERE login = ?',
            array($login)
        );
    }

    private function insertAccessRow(string $login, int $idSite, string $access): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('access') . ' (login, idsite, access) VALUES (?, ?, ?)',
            array($login, $idSite, $access)
        );
    }

    private function insertTokenRow(string $login): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('user_token_auth')
                . ' (login, description, password, hash_algo, date_created) VALUES (?, ?, ?, ?, ?)',
            array($login, 'MyDescription', hash('sha512', $login), 'sha512', Date::now()->getDatetime())
        );
    }

    private function createPendingUser(string $email = 'pending@pending.de'): array
    {
        $this->model->addUser($this->login3, '', $email, Date::now()->getDatetime());
        $this->model->attachInviteToken($this->login3, 'inviteToken', 7);

        return $this->model->getUser($this->login3);
    }

    private function expireInvitation(): void
    {
        $this->model->updateUserFields($this->login3, [
            'invite_expired_at' => Date::now()->subDay(1)->getDatetime(),
        ]);
    }

    private function invokeInviteTokenExists(string $token): bool
    {
        $method = new \ReflectionMethod(Model::class, 'inviteTokenExists');
        $method->setAccessible(true);

        return $method->invoke($this->model, $token);
    }

    private function insertSessionRowForLogin(string $login, $prependstring): void
    {
        $_SESSION = ['randomstring' => $prependstring]; // to move position of user.name to different positions
        $fingerprint = new SessionFingerprint();
        $fingerprint->initialize($login, 'foo');
        $sessionTable = Common::prefixTable('session');

        $data = serialize(\Zend_Session::buildSessionData($_SESSION));
        $_SESSION = [];

        Db::query(
            'INSERT INTO `' . $sessionTable . '` (`id`, `modified`, `lifetime`, `data`) VALUES (?, ?, ?, ?)',
            [hash('sha512', uniqid($login, true)), time(), 7200, $data]
        );
    }
}
