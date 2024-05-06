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
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
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
            array('site' => '2', 'access' => Write::ID)
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function testGetSitesAccessFromUserMultipleSites()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        $access = $this->model->getSitesAccessFromUser($this->login);
        // The order might differ depending on the database, so sort by 'idaction'
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
            'secure_only' => '0'
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
            'secure_only' => '0'
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
            'secure_only' => '0'
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
            '02c2e43dcb393097a1221465812a4e9b1e1e80f16e92b313fd4ce8c5ee5b8272a17cd8cdc1ce63578494eaba739c6f7abba7890506ef6bf8d607538778f2a849'
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
}
