<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests;

use Piwik\Access\Role\View;
use Piwik\Access\Role\Write;
use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Access\Role\Admin;


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

    public function setUp()
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
    }

    public function test_getSitesAccessFromUser_noAccess()
    {
        $this->assertSame(array(), $this->model->getSitesAccessFromUser($this->login));
    }

    public function test_getSitesAccessFromUser_accessOneSite()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->assertEquals(array(
            array('site' => '2', 'access' => Write::ID)
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function test_getSitesAccessFromUser_multipleSites()
    {
        $this->model->addUserAccess($this->login, Write::ID, array(3));
        $this->model->addUserAccess($this->login, Write::ID, array(2));
        $this->model->addUserAccess($this->login, View::ID, array(1));
        $this->assertEquals(array(
            array('site' => '3', 'access' => Write::ID),
            array('site' => '2', 'access' => Write::ID),
            array('site' => '1', 'access' => View::ID),
        ), $this->model->getSitesAccessFromUser($this->login));
    }

    public function test_getSitesAccessFromUser_multipleSitesSomeNoLongerExist()
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

    public function test_getSitesAccessFromUser_siteDeletedManually()
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

    public function test_getAllNonSystemTokensForLogin_whenNoTokenConfigured()
    {
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertSame(array(), $tokens);
    }

    public function test_addTokenAuth_minimal()
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
            'date_expired' => null
        )), $tokens);
    }

    public function test_addTokenAuth_expire()
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
            'date_expired' => '2030-01-05 03:04:05'
        )), $tokens);
    }

    /**
     * @expectedException  \Exception
     * @expectedExceptionMessage  does not exist
     */
    public function test_addTokenAuth_throwsException_ifUserNotExists()
    {
        $this->model->addTokenAuth('foobar', 'token', 'MyDescription', '2020-01-02 03:04:05', '2030-01-05 03:04:05');
    }

    /**
     * @expectedException  \Exception
     * @expectedExceptionMessage  General_ValidatorErrorEmptyValue
     */
    public function test_addTokenAuth_throwsException_NoDescription()
    {
        $this->model->addTokenAuth($this->login, 'token', '', '2020-01-02 03:04:05');
    }

    public function test_getAllNonSystemTokensForLogin_doesNotReturnSystemTokens()
    {
        $this->model->addTokenAuth($this->login, 'token2', 'api usage token', '2020-01-02 03:04:05', null, true);
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals(array(), $tokens);
    }

    public function test_getAllNonSystemTokensForLogin_doesNotReturnExpiredTokens()
    {
        $this->model->addTokenAuth($this->login, 'token2', 'api usage token', '2020-01-02 03:04:05', '2019-01-05 03:04:05');
        $tokens = $this->model->getAllNonSystemTokensForLogin($this->login);
        $this->assertEquals(array(), $tokens);
    }

    public function test_getAllNonSystemTokensForLogin_returnsNotExpiredToken()
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
            'date_expired' => '2030-01-05 03:04:05'
        )), $tokens);
    }

    public function test_getUserByTokenAuth_findsUserWhenTokenNotYetExpired()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', '2030-01-05 03:04:05');
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertSame($this->login, $user['login']);
    }

    public function test_getUserByTokenAuth_findsUserWhenNoExpireDateSet()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05');
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertSame($this->login, $user['login']);
    }

    public function test_getUserByTokenAuth_notFindsUserWhenTokenIsExpired()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', '2019-03-04 00:05:06');
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertEmpty($user);
    }

    public function test_getUserByTokenAuth_findsUserWhenTokenIsSystemToken()
    {
        $this->model->addTokenAuth($this->login, 'token', 'MyDescription', '2020-01-02 03:04:05', null, true);
        $user = $this->model->getUserByTokenAuth('token');
        $this->assertSame($this->login, $user['login']);
    }

    public function test_generateRandomTokenAuth_correctFormat()
    {
        $token = $this->model->generateRandomTokenAuth();
        $this->assertSame(32, strlen($token));
        $this->assertTrue(ctype_xdigit($token));
    }

    public function test_generateRandomTokenAuth_isAlwaysDifferent()
    {
        $this->assertNotEquals($this->model->generateRandomTokenAuth(), $this->model->generateRandomTokenAuth());
    }

    public function test_hashTokenAuth()
    {
        $this->assertSame('2265daba0872fc3aef169d079365e590f0cbc8ed46c2a7984c8a642803cfd96cb47804a63cf22a79f6ca469268c29ee9e72a5059b62d0a598fe42dfc8dcc51bc', $this->model->hashTokenAuth('token'));
        $this->assertSame('02c2e43dcb393097a1221465812a4e9b1e1e80f16e92b313fd4ce8c5ee5b8272a17cd8cdc1ce63578494eaba739c6f7abba7890506ef6bf8d607538778f2a849', $this->model->hashTokenAuth('token2'));
    }

    public function test_getAllHashedTokensForLogins_noLoginsSet()
    {
        $this->assertSame(array(), $this->model->getAllHashedTokensForLogins(array()));
    }

    public function test_getAllHashedTokensForLogins_noTokensExist()
    {
        $this->assertSame(array(), $this->model->getAllHashedTokensForLogins(array('foo', 'bar')));
    }

    public function test_getAllHashedTokensForLogins()
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

    public function test_setTokenAuthWasUsed()
    {
        $this->model->addTokenAuth($this->login, 'token2', 'MyDescription', '2020-01-02 03:04:05');
        $this->model->setTokenAuthWasUsed('token2',  '2025-01-02 03:04:05');

        $tokens = $this->model->getAllNonSystemTokensForLogin('token2');
        $this->assertSame('2025-01-02 03:04:05', $tokens[0]['last_used']);
    }

    public function test_setTokenAuthWasUsed_doesNotFailWhenTokenNotExists()
    {
        $this->model->setTokenAuthWasUsed('tokenFooBar',  '2025-01-02 03:04:05');
    }

}
