<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests;

use Piwik\Access\Role\View;
use Piwik\Access\Role\Write;
use Piwik\Auth\Password;
use Piwik\Config;
use Piwik\Mail;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Access\Role\Admin;
use Piwik\Access\Capability;

class TestCap1 extends Capability
{
    const ID = 'test_cap1';

    public function getId(): string
    {
        return self::ID;
    }

    public function getCategory(): string
    {
        return 'Test';
    }

    public function getName(): string
    {
        return 'Cap1';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getIncludedInRoles(): array
    {
        return array(
            Admin::ID
        );
    }
}

class TestCap2 extends Capability
{
    const ID = 'test_cap2';

    public function getId(): string
    {
        return self::ID;
    }

    public function getCategory(): string
    {
        return 'Test';
    }

    public function getName(): string
    {
        return 'Cap2';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getIncludedInRoles(): array
    {
        return array(
            Write::ID, Admin::ID
        );
    }
}

class TestCap3 extends Capability
{
    const ID = 'test_cap3';

    public function getId(): string
    {
        return self::ID;
    }

    public function getCategory(): string
    {
        return 'Test';
    }

    public function getName(): string
    {
        return 'Cap3';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getIncludedInRoles(): array
    {
        return array(Admin::ID);
    }
}


/**
 * @group UsersManager
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
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

    private $password = 'password';

    private $email = 'userlogin@password.de';

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
        $this->api->addUser($this->login, $this->password, $this->email);
    }
    
    public function tearDown(): void
    {
        Config::getInstance()->General['enable_update_users_email'] = 1;

        parent::tearDown(); 
    }
    
    public function test_setUserAccess_ShouldTriggerRemoveSiteAccessEvent_IfAccessToAWebsiteIsRemoved()
    {
        $eventTriggered = false;
        $self = $this;
        Piwik::addAction('UsersManager.removeSiteAccess', function ($login, $idSites) use (&$eventTriggered, $self) {
            $eventTriggered = true;
            $self->assertEquals($self->login, $login);
            $self->assertEquals(array(1, 2), $idSites);
        });

        $this->api->setUserAccess($this->login, 'noaccess', array(1, 2));

        $this->assertTrue($eventTriggered, 'UsersManager.removeSiteAccess event was not triggered');
    }

    public function test_setUserAccess_ShouldNotTriggerRemoveSiteAccessEvent_IfAccessIsAdded()
    {
        $eventTriggered = false;
        Piwik::addAction('UsersManager.removeSiteAccess', function () use (&$eventTriggered) {
            $eventTriggered = true;
        });

        $this->api->setUserAccess($this->login, 'admin', array(1, 2));

        $this->assertFalse($eventTriggered, 'UsersManager.removeSiteAccess event was triggered but should not');
    }

    public function test_getAllUsersPreferences_isEmpty_whenNoPreference()
    {
        $preferences = $this->api->getAllUsersPreferences(array('preferenceName'));
        $this->assertEmpty($preferences);
    }

    public function test_getAllUsersPreferences_isEmpty_whenNoPreferenceAndMultipleRequested()
    {
        $preferences = $this->api->getAllUsersPreferences(array('preferenceName', 'randomDoesNotExist'));
        $this->assertEmpty($preferences);
    }

    public function test_getUserPreference_ShouldReturnADefaultPreference_IfNoneIsSet()
    {
        $siteId = $this->api->getUserPreference(API::PREFERENCE_DEFAULT_REPORT, $this->login);
        $this->assertEquals('1', $siteId);
    }

    public function test_getUserPreference_ShouldReturnASetreference_IfNoneIsSet()
    {
        $this->api->setUserPreference($this->login, API::PREFERENCE_DEFAULT_REPORT, 5);

        $siteId = $this->api->getUserPreference(API::PREFERENCE_DEFAULT_REPORT, $this->login);
        $this->assertEquals('5', $siteId);
    }

    public function test_initUserPreferenceWithDefault_ShouldSaveTheDefaultPreference_IfPreferenceIsNotSet()
    {
        // make sure there is no value saved so it will use default preference
        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        $this->assertFalse($siteId);

        $this->api->initUserPreferenceWithDefault($this->login, API::PREFERENCE_DEFAULT_REPORT);

        // make sure it did save the preference
        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        $this->assertEquals('1', $siteId);
    }

    public function test_initUserPreferenceWithDefault_ShouldNotSaveTheDefaultPreference_IfPreferenceIsAlreadySet()
    {
        // set value so there will already be a default
        Option::set($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT), '999');

        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        $this->assertEquals('999', $siteId);

        $this->api->initUserPreferenceWithDefault($this->login, API::PREFERENCE_DEFAULT_REPORT);

        // make sure it did not save the preference
        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        $this->assertEquals('999', $siteId);
    }

    public function test_getAllUsersPreferences_shouldGetMultiplePreferences()
    {
        $user2 = 'userLogin2';
        $user3 = 'userLogin3';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');
        $this->api->setUserPreference($user2, API::PREFERENCE_DEFAULT_REPORT, 'valueForUser2');
        $this->api->setUserPreference($user2, 'RandomNOTREQUESTED', 'RandomNOTREQUESTED');

        $this->api->addUser($user3, 'password', 'userlogin3@password.de');
        $this->api->setUserPreference($user3, API::PREFERENCE_DEFAULT_REPORT, 'valueForUser3');
        $this->api->setUserPreference($user3, API::PREFERENCE_DEFAULT_REPORT_DATE, 'otherPreferenceVALUE');
        $this->api->setUserPreference($user3, 'RandomNOTREQUESTED', 'RandomNOTREQUESTED');

        $expected = array(
            $user2 => array(
                API::PREFERENCE_DEFAULT_REPORT => 'valueForUser2'
            ),
            $user3 => array(
                API::PREFERENCE_DEFAULT_REPORT => 'valueForUser3',
                API::PREFERENCE_DEFAULT_REPORT_DATE => 'otherPreferenceVALUE',
            ),
        );
        $result = $this->api->getAllUsersPreferences(array(API::PREFERENCE_DEFAULT_REPORT, API::PREFERENCE_DEFAULT_REPORT_DATE, 'randomDoesNotExist'));

        $this->assertSame($expected, $result);
    }

    public function test_getAllUsersPreferences_whenLoginContainsUnderscore()
    {
        $user2 = 'user_Login2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');
        $this->api->setUserPreference($user2, API::PREFERENCE_DEFAULT_REPORT, 'valueForUser2');
        $this->api->setUserPreference($user2, API::PREFERENCE_DEFAULT_REPORT_DATE, 'RandomNOTREQUESTED');

        $expected = array(
            $user2 => array(
                API::PREFERENCE_DEFAULT_REPORT => 'valueForUser2'
            ),
        );
        $result = $this->api->getAllUsersPreferences(array(API::PREFERENCE_DEFAULT_REPORT, 'randomDoesNotExist'));

        $this->assertSame($expected, $result);
    }

    public function test_setUserPreference_throws_whenPreferenceNameContainsUnderscore()
    {
        $this->expectException(\Exception::class);

        $user2 = 'userLogin2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');
        $this->api->setUserPreference($user2, 'ohOH_myPreferenceName', 'valueForUser2');
    }

    public function test_updateUser()
    {
        $capturedMails = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        $identity = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // ensure password will be checked against this user
        $this->api->updateUser($this->login, 'newPassword', 'email@example.com',  false, $this->password);
        FakeAccess::$identity = $identity;

        $model = new Model();
        $user = $model->getUser($this->login);

        $this->assertSame('email@example.com', $user['email']);

        $passwordHelper = new Password();

        $this->assertTrue($passwordHelper->verify(UsersManager::getPasswordHash('newPassword'), $user['password']));

        $subjects = array_map(function (Mail $mail) { return $mail->getSubject(); }, $capturedMails);
        $this->assertEquals([
            'UsersManager_EmailChangeNotificationSubject', // sent twice to old email and new
            'UsersManager_EmailChangeNotificationSubject',
            'UsersManager_PasswordChangeNotificationSubject',
        ], $subjects);
    }

    public function test_updateUser_doesNotSendEmailsIfTurnedOffInConfig()
    {
        Config::getInstance()->General['enable_update_users_email'] = 0;
        $capturedMails = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        $identity = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // en
        $this->api->updateUser($this->login, 'newPassword2', 'email2@example.com', false, $this->password);
        FakeAccess::$identity = $identity;

        $subjects = array_map(function (Mail $mail) { return $mail->getSubject(); }, $capturedMails);
        $this->assertEquals([], $subjects);
    }


    public function test_updateUser_doesNotSendEmailIfNoChangeAndDoesNotRequirePassword()
    {
        $capturedMails = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        $identity = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // en
        $this->api->updateUser($this->login, false, strtoupper($this->email));
        FakeAccess::$identity = $identity;

        $this->assertEquals([], $capturedMails);
    }

    public function test_updateUser_doesNotChangePasswordIfFalsey()
    {
        $model = new Model();
        $userBefore = $model->getUser($this->login);

        $identity = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // ensure password will be checked against this user
        $this->api->updateUser($this->login, false, 'email@example.com', false, $this->password);
        FakeAccess::$identity = $identity;

        $user = $model->getUser($this->login);

        $this->assertSame($userBefore['password'], $user['password']);
        $this->assertSame($userBefore['ts_password_modified'], $user['ts_password_modified']);
    }

    public function test_updateUser_failsIfPasswordTooLong()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionInvalidPasswordTooLong');

        $this->api->updateUser($this->login, str_pad('foo', UsersManager::PASSWORD_MAX_LENGTH + 1), 'email@example.com', false, $this->password);
    }

    public function test_getSitesAccessFromUser_forSuperUser()
    {
        $user2 = 'userLogin2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');

        // new user doesn't have access to anything
        $access = $this->api->getSitesAccessFromUser($user2);
        $this->assertEmpty($access);

        $userUpdater = new UserUpdater();
        $userUpdater->setSuperUserAccessWithoutCurrentPassword($user2, true);

        // super user has admin access for every site
        $access = $this->api->getSitesAccessFromUser($user2);
        $expected = array(
            array(
                'site' => 1,
                'access' => 'admin'
            ),
            array(
                'site' => 2,
                'access' => 'admin'
            ),
            array(
                'site' => 3,
                'access' => 'admin'
            ),
        );
        $this->assertEquals($expected, $access);
    }

    public function test_getUsersPlusRole_shouldReturnSelfIfUserDoesNotHaveAdminAccessToSite()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->setCurrentUser('userLogin2', 'view', 1);

        $users = $this->api->getUsersPlusRole(1);
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin2', 'role' => 'view', 'capabilities' => [], 'email' => 'userLogin2@password.de', 'superuser_access' => '0'],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldIgnoreOffsetIfLimitIsNotSupplied()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->setCurrentUser('userLogin2', 'view', 1);

        $users = $this->api->getUsersPlusRole(1, $limit = null, $offset = 1);
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin2', 'role' => 'view', 'capabilities' => [], 'email' => 'userLogin2@password.de', 'superuser_access' => '0'],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldNotAllowSuperuserFilter_ifUserIsNotSuperUser()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->addUserWithAccess('userLogin3', 'superuser', 1);
        $this->setCurrentUser('userLogin2', 'view', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'superuser');
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin2', 'role' => 'view', 'capabilities' => [], 'email' => 'userLogin2@password.de', 'superuser_access' => '0'],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldReturnAllUsersAndAccess_ifUserHasAdminAccess()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'admin', 1);
        $this->addUserWithAccess('userLogin5', null, 1);
        $this->setCurrentUser('userLogin2', 'admin', 1);

        $users = $this->api->getUsersPlusRole(1);
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin2', 'role' => 'admin', 'capabilities' => [], 'email' => 'userLogin2@password.de', 'superuser_access' => false],
            ['login' => 'userLogin3', 'role' => 'view', 'capabilities' => [], 'superuser_access' => false],
            ['login' => 'userLogin4', 'role' => 'admin', 'capabilities' => [], 'superuser_access' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldLimitUsersReturnedToThoseWithAccessToSitesAsCurrentUsersAdminSites_IfCurrentUserIsAdmin()
    {
        $this->addUserWithAccess('userLogin2', 'admin', [1, 2]);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'admin', 1);
        $this->addUserWithAccess('userLogin5', null, [1, 2]);
        $this->api->setUserAccess('userLogin5', 'view', 2);
        $this->setCurrentUser('userLogin2', 'admin', [1, 2]);

        $users = $this->api->getUsersPlusRole(1);
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin2', 'role' => 'admin', 'capabilities' => [], 'email' => 'userLogin2@password.de', 'superuser_access' => false],
            ['login' => 'userLogin3', 'role' => 'view', 'capabilities' => [], 'superuser_access' => false],
            ['login' => 'userLogin4', 'role' => 'admin', 'capabilities' => [], 'superuser_access' => false],
            ['login' => 'userLogin5', 'role' => 'noaccess', 'capabilities' => [], 'superuser_access' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldReturnAllUsersAndAccess_ifUserHasSuperuserAccess()
    {
        $this->addUserWithAccess('userLogin2', 'superuser', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', null, 1);
        $this->setCurrentUser('userLogin2', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1);
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin', 'email' => 'userlogin@password.de', 'superuser_access' => false, 'role' => 'noaccess', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin2', 'email' => 'userLogin2@password.de', 'superuser_access' => true, 'role' => 'superuser', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin3', 'email' => 'userLogin3@password.de', 'superuser_access' => false, 'role' => 'view', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin4', 'email' => 'userLogin4@password.de', 'superuser_access' => true, 'role' => 'superuser', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin5', 'email' => 'userLogin5@password.de', 'superuser_access' => false, 'role' => 'noaccess', 'capabilities' => [], 'uses_2fa' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldFilterUsersByAccessCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', 'admin', 1);
        $this->addUserWithAccess('userLogin6', 'write', 1);
        $this->setCurrentUser('userLogin2', 'admin', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'admin');
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin2', 'role' => 'admin', 'capabilities' => [], 'email' => 'userLogin2@password.de', 'superuser_access' => false],
            ['login' => 'userLogin5', 'role' => 'admin', 'capabilities' => [], 'superuser_access' => false],
        ];
        $this->assertEquals($expected, $users);

        // check new write role filtering works
        $users = $this->api->getUsersPlusRole(1, null, null, null, 'write');
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin6', 'role' => 'write', 'capabilities' => [], 'superuser_access' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldReturnUsersWithNoAccessCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'noaccess', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', 'noaccess', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'noaccess');
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin', 'role' => 'noaccess', 'superuser_access' => false, 'email' => 'userlogin@password.de', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin2', 'role' => 'noaccess', 'superuser_access' => false, 'email' => 'userLogin2@password.de', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin5', 'role' => 'noaccess', 'superuser_access' => false, 'email' => 'userLogin5@password.de', 'capabilities' => [], 'uses_2fa' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldSearchForSuperUsersCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $userUpdater = new UserUpdater();
        $userUpdater->setSuperUserAccessWithoutCurrentPassword('userLogin2', true);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', null, 1);
        $this->setCurrentUser('userLogin2', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'superuser');
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin2', 'email' => 'userLogin2@password.de', 'superuser_access' => true, 'role' => 'superuser', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin4', 'email' => 'userLogin4@password.de', 'superuser_access' => true, 'role' => 'superuser', 'capabilities' => [], 'uses_2fa' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldSearchByTextCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'superuser', 1, 'someemail@email.com');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', 'superuser', 1, 'someemail2@email.com');
        $this->addUserWithAccess('userLogin4', null, 1);
        $this->setCurrentUser('searchTextLogin', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, 'searchText');
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'searchTextLogin', 'email' => 'someemail@email.com', 'superuser_access' => true, 'role' => 'superuser', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin2', 'email' => 'searchTextdef@email.com', 'superuser_access' => false, 'role' => 'view', 'capabilities' => [], 'uses_2fa' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldApplyLimitAndOffsetCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'superuser', 1, 'someemail@email.com');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', 'superuser', 1, 'someemail2@email.com');
        $this->addUserWithAccess('userLogin4', null, 1);
        $this->setCurrentUser('searchTextLogin', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, $limit = 2, $offset = 1);
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin',  'email' => 'userlogin@password.de', 'superuser_access' => false, 'role' => 'noaccess', 'capabilities' => [], 'uses_2fa' => false],
            ['login' => 'userLogin2', 'email' => 'searchTextdef@email.com', 'superuser_access' => false, 'role' => 'view', 'capabilities' => [], 'uses_2fa' => false],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getSitesAccessForUser_shouldReturnAccessForUser()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access = $this->api->getSitesAccessForUser('userLogin');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'admin', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function getSitesAccessForUser_shouldIgnoreOffsetIfLimitNotSupplied()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access = $this->api->getSitesAccessForUser('userLogin', $limit = null, $offset = 1);
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_getSitesAccessForUser_shouldApplyLimitAndOffsetCorrectly()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access = $this->api->getSitesAccessForUser('userLogin', $limit = 2, $offset = 1);
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_getSitesAccessForUser_shouldSearchSitesCorrectly()
    {
        Fixture::createWebsite('2010-01-02 00:00:00');

        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);
        $this->api->setUserAccess('userLogin', 'view', [4]);

        SitesManagerAPI::getInstance()->updateSite(1, 'searchTerm site');
        SitesManagerAPI::getInstance()->updateSite(2, null, ['http://searchTerm.com']);
        SitesManagerAPI::getInstance()->updateSite(3, null, null, null, null, null, null, null, null, null, null, 'the searchTerm group');

        $access = $this->api->getSitesAccessForUser('userLogin', null, null, 'searchTerm');
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '1', 'site_name' => 'searchTerm site', 'role' => 'admin', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_getSitesAccessForUser_shouldFilterByAccessCorrectly()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access = $this->api->getSitesAccessForUser('userLogin', null, null, null, 'view');
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_getSitesAccessForUser_shouldLimitSitesIfUserIsAdmin()
    {
        $this->addUserWithAccess('userLogin2', 'view', [1, 2, 3], 'userlogin2@email.com');

        $this->api->setUserAccess('userLogin', 'admin', [1, 2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $this->setCurrentUser('userLogin', 'admin', [1, 2]);

        $access = $this->api->getSitesAccessForUser('userLogin2', null, null, null, 'view');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_getSitesAccessForUser_shouldLimitSitesIfUserIsAdmin_AndStillSelectNoAccessSitesCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'view', [1], 'userlogin2@email.com');

        $this->api->setUserAccess('userLogin', 'admin', [1, 2, 3]);

        $this->setCurrentUser('userLogin', 'admin', [1, 2, 3]);

        $access = $this->api->getSitesAccessForUser('userLogin2', null, null, null, 'noaccess');
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_getSitesAccessForUser_shouldSelectSitesCorrectlyIfAtLeastViewRequested()
    {
        $this->addUserWithAccess('userLogin2', 'view', [1], 'userlogin2@email.com');
        $this->api->setUserAccess('userLogin2', 'admin', [2]);

        $access = $this->api->getSitesAccessForUser('userLogin2', null, null, null, 'some');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'admin', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_getSitesAccessForUser_shouldReportIfUserHasNoAccessToSites()
    {
        $access = $this->api->getSitesAccessForUser('userLogin');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
        ];
        $this->assertEquals($expected, $access);

        // test when search returns empty result
        $this->api->setUserAccess('userLogin', 'view', 1);

        $access = $this->api->getSitesAccessForUser('userLogin', null, null, 'asdklfjds');
        $expected = [];
        $this->assertEquals($expected, $access);
    }

    public function test_setUserAccess_MultipleRolesCannotBeSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionMultipleRoleSet');

        $this->api->setUserAccess($this->login, array('view', 'admin'), array(1));
    }

    public function test_setUserAccess_NeedsAtLeastOneRole()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionNoRoleSet');

        $this->api->setUserAccess($this->login, array(TestCap2::ID), array(1));
    }

    public function test_setUserAccess_NeedsAtLeastOneRoleAsString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->setUserAccess($this->login, TestCap2::ID, array(1));
    }

    public function test_setUserAccess_InvalidCapability()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->setUserAccess($this->login, array('admin', 'foobar'), array(1));
    }

    public function test_setUserAccess_NeedsAtLeastOneRoleNoneGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionNoRoleSet');

        $this->api->setUserAccess($this->login, array(), array(1));
    }

    public function test_setUserAccess_CannotSetAdminToAnonymous()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAnonymousAccessNotPossible');

        $this->api->setUserAccess('anonymous', 'admin', array(1));
    }

    public function test_setUserAccess_CannotSetWriteToAnonymous()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAnonymousAccessNotPossible');

        $this->api->setUserAccess('anonymous', 'write', array(1));
    }

    public function test_setUserAccess_UserDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->api->setUserAccess('foobar', Admin::ID, array(1));
    }

    public function test_setUserAccess_SetRoleAndCapabilities()
    {
        $access = array(TestCap2::ID, View::ID, TestCap3::ID);
        $this->api->setUserAccess($this->login, $access, array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = array(
            array('site' => '1', 'access' => 'view'),
            array('site' => '1', 'access' => TestCap2::ID),
            array('site' => '1', 'access' => TestCap3::ID),
        );
        $this->assertEquals($expected, $access);
    }

    public function test_setUserAccess_SetRoleAsString()
    {
        $this->api->setUserAccess($this->login, View::ID, array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);
        $this->assertEquals(array(array('site' => '1', 'access' => 'view')), $access);
    }

    public function test_setUserAccess_SetRoleAsArray()
    {
        $this->api->setUserAccess($this->login, array(View::ID), array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);
        $this->assertEquals(array(array('site' => '1', 'access' => 'view')), $access);
    }

    public function test_addCapabilities_failsWhenNotCapabilityIsGivenAsString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->addCapabilities($this->login, View::ID, array(1));
    }

    public function test_addCapabilities_failsWhenNotCapabilityIsGivenAsArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->addCapabilities($this->login, array(TestCap2::ID, View::ID), array(1));
    }

    public function test_addCapabilities_failsWhenUserDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->api->addCapabilities('foobar', array(TestCap2::ID), array(1));
    }

    public function test_addCapabilities_DoesNotAddSameCapabilityTwice()
    {
        $addAccess = array(TestCap2::ID, View::ID, TestCap3::ID);
        $this->api->setUserAccess($this->login, $addAccess, array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = array(
            array('site' => '1', 'access' => 'view'),
            array('site' => '1', 'access' => TestCap2::ID),
            array('site' => '1', 'access' => TestCap3::ID),
        );
        $this->assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, array(TestCap2::ID, TestCap3::ID), array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);
        $this->assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, array(TestCap2::ID, TestCap1::ID, TestCap3::ID), array(1));

        $expected[] = array('site' => '1', 'access' => TestCap1::ID);
        $access = $this->model->getSitesAccessFromUser($this->login);
        $this->assertEquals($expected, $access);
    }

    public function test_addCapabilities_DoesNotAddCapabilityToUserWithNoRole()
    {
        $access = $this->model->getSitesAccessFromUser($this->login);

        $this->assertEquals(array(), $access);

        $this->api->addCapabilities($this->login, array(TestCap2::ID, TestCap3::ID), array(1));

        $this->assertEquals(array(), $access);
    }

    public function test_addCapabilities_DoesNotAddCapabilitiesWhichAreIncludedInRoleAlready()
    {
        $this->api->setUserAccess($this->login, Write::ID, array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = array(
            array('site' => '1', 'access' => 'write'),
        );
        $this->assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, array(TestCap2::ID, TestCap3::ID), array(1));

        $expected[] = array('site' => '1', 'access' => TestCap3::ID);
        $access = $this->model->getSitesAccessFromUser($this->login);

        // did not add TestCap2
        $this->assertEquals($expected, $access);
    }

    public function test_addCapabilities_DoesAddCapabilitiesWhichAreNotIncludedInRoleYetAlready()
    {
        $this->api->setUserAccess($this->login, Admin::ID, array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = array(
            array('site' => '1', 'access' => 'admin'),
        );
        $this->assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, array(TestCap2::ID, TestCap1::ID, TestCap3::ID), array(1));

        $access = $this->model->getSitesAccessFromUser($this->login);
        $this->assertEquals($expected, $access);
    }

    public function test_removeCapabilities_failsWhenNotCapabilityIsGivenAsString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->removeCapabilities($this->login, View::ID, array(1));
    }

    public function test_removeCapabilities_failsWhenNotCapabilityIsGivenAsArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->removeCapabilities($this->login, array(TestCap2::ID, View::ID), array(1));
    }

    public function test_removeCapabilities_failsWhenUserDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->api->removeCapabilities('foobar', array(TestCap2::ID), array(1));
    }

    public function test_removeCapabilities()
    {
        $addAccess = array(View::ID, TestCap2::ID, TestCap3::ID, TestCap1::ID);
        $this->api->setUserAccess($this->login, $addAccess, array(1));

        $access = $this->getAccessInSite($this->login, 1);
        $this->assertEquals($addAccess, $access);

        $this->api->removeCapabilities($this->login, array(TestCap3::ID, TestCap2::ID), 1);

        $access = $this->getAccessInSite($this->login, 1);
        $this->assertEquals(array(View::ID, TestCap1::ID), $access);
    }

    public function test_setSuperUserAccess_failsIfCurrentPasswordIsIncorrect()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_CurrentPasswordNotCorrect');

        $this->api->setSuperUserAccess($this->login, true, 'asldfkjds');
    }

    private function getAccessInSite($login, $idSite)
    {
        $access = $this->model->getSitesAccessFromUser($login);
        $ids = array();
        foreach ($access as $entry) {
            if ($entry['site'] == $idSite) {
                $ids[] = $entry['access'];
            }
        }
        return $ids;
    }

    private function getPreferenceId($preferenceName)
    {
        return $this->login . '_' . $preferenceName;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
            'usersmanager.user_preference_names' => \DI\add(['randomDoesNotExist', 'RandomNOTREQUESTED', 'preferenceName']),
            'observers.global' => \DI\add([
                ['Access.Capability.addCapabilities', \DI\value(function (&$capabilities) {
                    $capabilities[] = new TestCap1();
                    $capabilities[] = new TestCap2();
                    $capabilities[] = new TestCap3();
                })],
            ]),
        );
    }

    private function addUserWithAccess($username, $accessLevel, $idSite, $email = null)
    {
        $this->api->addUser($username, 'password', $email ?: "$username@password.de");
        if ($accessLevel == 'superuser') {
            $userUpdater = new UserUpdater();
            $userUpdater->setSuperUserAccessWithoutCurrentPassword($username, true);
        } else if ($accessLevel) {
            $this->api->setUserAccess($username, $accessLevel, $idSite);
        }
    }

    public function setCurrentUser($username, $accessLevel, $idSite)
    {
        FakeAccess::$identity = $username;
        FakeAccess::$superUser = $accessLevel == 'superuser';
        if ($accessLevel == 'view') {
            FakeAccess::$idSitesView = is_array($idSite) ? $idSite : [$idSite];
        } else if ($accessLevel == 'admin') {
            FakeAccess::$idSitesAdmin = is_array($idSite) ? $idSite : [$idSite];
        }
    }

    private function cleanUsers(&$users)
    {
        foreach ($users as &$user) {
            unset($user['date_registered']);
        }
    }
}
