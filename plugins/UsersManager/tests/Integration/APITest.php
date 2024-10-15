<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Access\Capability;
use Piwik\Access\Role\Admin;
use Piwik\Access\Role\View;
use Piwik\Access\Role\Write;
use Piwik\API\Request;
use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\EventDispatcher;
use Piwik\Mail;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Emails\UserCreatedEmail;
use Piwik\Plugins\UsersManager\Emails\UserInviteEmail;
use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestCap1 extends Capability
{
    public const ID = 'test_cap1';

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
        return [
            Admin::ID,
        ];
    }
}

class TestCap2 extends Capability
{
    public const ID = 'test_cap2';

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
        return [
            Write::ID,
            Admin::ID,
        ];
    }
}

class TestCap3 extends Capability
{
    public const ID = 'test_cap3';

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
        return [Admin::ID];
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

        $this->api   = API::getInstance();
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

    public function testSetUserAccessShouldTriggerRemoveSiteAccessEventIfAccessToAWebsiteIsRemoved()
    {
        $eventTriggered = false;
        $self           = $this;
        Piwik::addAction('UsersManager.removeSiteAccess', function ($login, $idSites) use (&$eventTriggered, $self) {
            $eventTriggered = true;
            self::assertEquals($self->login, $login);
            self::assertEquals([1, 2], $idSites);
        });

        $this->api->setUserAccess($this->login, 'noaccess', [1, 2]);

        self::assertTrue($eventTriggered, 'UsersManager.removeSiteAccess event was not triggered');
    }

    public function testSetUserAccessShouldNotTriggerRemoveSiteAccessEventIfAccessIsAdded()
    {
        $eventTriggered = false;
        Piwik::addAction('UsersManager.removeSiteAccess', function () use (&$eventTriggered) {
            $eventTriggered = true;
        });

        $this->api->setUserAccess($this->login, 'admin', [1, 2]);

        self::assertFalse($eventTriggered, 'UsersManager.removeSiteAccess event was triggered but should not');
    }

    public function testGetAllUsersPreferencesIsEmptyWhenNoPreference()
    {
        $preferences = $this->api->getAllUsersPreferences(['preferenceName']);
        self::assertEmpty($preferences);
    }

    public function testGetAllUsersPreferencesIsEmptyWhenNoPreferenceAndMultipleRequested()
    {
        $preferences = $this->api->getAllUsersPreferences(['preferenceName', 'randomDoesNotExist']);
        self::assertEmpty($preferences);
    }

    public function testGetUserPreferenceShouldReturnADefaultPreferenceIfNoneIsSet()
    {
        $siteId = $this->api->getUserPreference(API::PREFERENCE_DEFAULT_REPORT, $this->login);
        self::assertEquals('1', $siteId);
    }

    public function testGetUserPreferenceShouldReturnASetreferenceIfNoneIsSet()
    {
        $this->api->setUserPreference($this->login, API::PREFERENCE_DEFAULT_REPORT, 5);

        $siteId = $this->api->getUserPreference(API::PREFERENCE_DEFAULT_REPORT, $this->login);
        self::assertEquals('5', $siteId);
    }

    public function testInitUserPreferenceWithDefaultShouldSaveTheDefaultPreferenceIfPreferenceIsNotSet()
    {
        // make sure there is no value saved so it will use default preference
        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        self::assertFalse($siteId);

        $this->api->initUserPreferenceWithDefault($this->login, API::PREFERENCE_DEFAULT_REPORT);

        // make sure it did save the preference
        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        self::assertEquals('1', $siteId);
    }

    public function testInitUserPreferenceWithDefaultShouldNotSaveTheDefaultPreferenceIfPreferenceIsAlreadySet()
    {
        // set value so there will already be a default
        Option::set($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT), '999');

        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        self::assertEquals('999', $siteId);

        $this->api->initUserPreferenceWithDefault($this->login, API::PREFERENCE_DEFAULT_REPORT);

        // make sure it did not save the preference
        $siteId = Option::get($this->getPreferenceId(API::PREFERENCE_DEFAULT_REPORT));
        self::assertEquals('999', $siteId);
    }

    public function testGetAllUsersPreferencesShouldGetMultiplePreferences()
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

        $expected = [
            $user2 => [
                API::PREFERENCE_DEFAULT_REPORT => 'valueForUser2',
            ],
            $user3 => [
                API::PREFERENCE_DEFAULT_REPORT      => 'valueForUser3',
                API::PREFERENCE_DEFAULT_REPORT_DATE => 'otherPreferenceVALUE',
            ],
        ];
        $result   = $this->api->getAllUsersPreferences([
                                                           API::PREFERENCE_DEFAULT_REPORT,
                                                           API::PREFERENCE_DEFAULT_REPORT_DATE,
                                                           'randomDoesNotExist',
                                                       ]);

        self::assertSame($expected, $result);
    }

    public function testGetAllUsersPreferencesWhenLoginContainsUnderscore()
    {
        $user2 = 'user_Login2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');
        $this->api->setUserPreference($user2, API::PREFERENCE_DEFAULT_REPORT, 'valueForUser2');
        $this->api->setUserPreference($user2, API::PREFERENCE_DEFAULT_REPORT_DATE, 'RandomNOTREQUESTED');

        $expected = [
            $user2 => [
                API::PREFERENCE_DEFAULT_REPORT => 'valueForUser2',
            ],
        ];
        $result   = $this->api->getAllUsersPreferences([API::PREFERENCE_DEFAULT_REPORT, 'randomDoesNotExist']);

        self::assertSame($expected, $result);
    }

    public function testSetUserPreferenceThrowsWhenPreferenceNameContainsUnderscore()
    {
        $this->expectException(\Exception::class);

        $user2 = 'userLogin2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');
        $this->api->setUserPreference($user2, 'ohOH_myPreferenceName', 'valueForUser2');
    }

    public function testAddUserFailsWhenEmailDomainNotAllowed()
    {
        $this->expectExceptionMessage('UsersManager_ErrorEmailDomainNotAllowed');

        $settings = StaticContainer::get(SystemSettings::class);
        $settings->allowedEmailDomains->setValue(['example.org', 'password.de', 'matomo.com']);

        $this->api->addUser('userLogin2', 'password', 'userlogin2@password.com');
    }

    public function testUpdateUser()
    {
        $capturedMails = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        $identity             = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // ensure password will be checked against this user
        $this->api->updateUser($this->login, 'newPassword', 'email@example.com', false, $this->password);
        FakeAccess::$identity = $identity;

        $model = new Model();
        $user  = $model->getUser($this->login);

        self::assertSame('email@example.com', $user['email']);

        $passwordHelper = new Password();

        self::assertTrue($passwordHelper->verify(UsersManager::getPasswordHash('newPassword'), $user['password']));

        $subjects = array_map(function (Mail $mail) {
            return $mail->getSubject();
        }, $capturedMails);
        self::assertEquals([
                                'UsersManager_EmailChangeNotificationSubject', // sent twice to old email and new
                                'UsersManager_EmailChangeNotificationSubject',
                                'UsersManager_PasswordChangeNotificationSubject',
                            ], $subjects);
    }


    public function testUpdateUserFailsWhenEmailDomainNotAllowed()
    {
        $this->expectExceptionMessage('UsersManager_ErrorEmailDomainNotAllowed');

        $settings = StaticContainer::get(SystemSettings::class);
        $settings->allowedEmailDomains->setValue(['example.org', 'password.de', 'matomo.com']);

        $this->api->addUser('userLogin2', 'passwordtest12', 'userlogin2@password.de');

        $this->api->updateUser('userLogin2', false, 'email@example.com', false, 'passwordtest12');
    }

    public function testUpdateUserDoesNotSendEmailsIfTurnedOffInConfig()
    {
        Config::getInstance()->General['enable_update_users_email'] = 0;
        $capturedMails                                              = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        $identity             = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // en
        $this->api->updateUser($this->login, 'newPassword2', 'email2@example.com', false, $this->password);
        FakeAccess::$identity = $identity;

        $subjects = array_map(function (Mail $mail) {
            return $mail->getSubject();
        }, $capturedMails);
        self::assertEquals([], $subjects);
    }


    public function testUpdateUserDoesNotSendEmailIfNoChangeAndDoesNotRequirePassword()
    {
        $capturedMails = [];
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        $identity             = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // en
        $this->api->updateUser($this->login, false, strtoupper($this->email));
        FakeAccess::$identity = $identity;

        self::assertEquals([], $capturedMails);
    }

    public function testUpdateUserDoesNotChangePasswordIfFalsey()
    {
        $model      = new Model();
        $userBefore = $model->getUser($this->login);

        $identity             = FakeAccess::$identity;
        FakeAccess::$identity = $this->login; // ensure password will be checked against this user
        $this->api->updateUser($this->login, false, 'email@example.com', false, $this->password);
        FakeAccess::$identity = $identity;

        $user = $model->getUser($this->login);

        self::assertSame($userBefore['password'], $user['password']);
        self::assertSame($userBefore['ts_password_modified'], $user['ts_password_modified']);
    }

    public function testUpdateUserFailsIfPasswordTooLong()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionInvalidPasswordTooLong');

        $this->api->updateUser(
            $this->login,
            str_pad('foo', UsersManager::PASSWORD_MAX_LENGTH + 1),
            'email@example.com',
            false,
            $this->password
        );
    }

    public function testUpdateUserFailsIfEmailExistsAsOtherUserUsername()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionEmailExistsAsLogin');

        $user2 = 'existed@example.com';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');

        $this->api->updateUser($this->login, $this->password, $user2, false, $this->password);
    }

    public function testUpdateCanUpdateUserEmailToOwnUsername()
    {
        $user2    = 'ownemail@example.com';
        $password = 'password';
        $this->api->addUser($user2, $password, 'ownemail_wrong@example.com');

        FakeAccess::$identity = $user2;
        $this->api->updateUser($user2, $password, $user2, false, $password);

        $user2Array = $this->api->getUser($user2);
        self::assertEquals($user2Array['email'], $user2);
    }

    public function testCannotCreateUserIfEmailExistsAsUsername()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionEmailExistsAsLogin');

        $user2 = 'existed@example.com';
        $this->api->addUser($user2, 'password', 'email@example.com');

        $this->api->addUser('user3', 'password', $user2);
    }

    public function testCannotCreateUserIfUsernameExistsAsEmail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionLoginExistsAsEmail');

        $this->api->addUser($this->email, 'password', 'new_user@example.com');
    }

    public function testGetSitesAccessFromUserForSuperUser()
    {
        $user2 = 'userLogin2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');

        // new user doesn't have access to anything
        $access = $this->api->getSitesAccessFromUser($user2);
        self::assertEmpty($access);

        $userUpdater = new UserUpdater();
        $userUpdater->setSuperUserAccessWithoutCurrentPassword($user2, true);

        // super user has admin access for every site
        $access   = $this->api->getSitesAccessFromUser($user2);
        $expected = [
            [
                'site'   => 1,
                'access' => 'admin',
            ],
            [
                'site'   => 2,
                'access' => 'admin',
            ],
            [
                'site'   => 3,
                'access' => 'admin',
            ],
        ];
        self::assertEquals($expected, $access);
    }

    public function testGetUsersPlusRoleShouldReturnNothingForAnonymousUser()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->setCurrentUser('anonymous', 'view', 1);

        $users = $this->api->getUsersPlusRole(1);
        self::assertEquals([], $users);
        self::assertResultCountHeader(0);
    }

    public function testGetUsersPlusRoleShouldReturnSelfIfUserDoesNotHaveAdminAccessToSite()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->setCurrentUser('userLogin2', 'view', 1);

        $users = $this->api->getUsersPlusRole(1);
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'userLogin2',
                'role'             => 'view',
                'capabilities'     => [],
                'email'            => 'userLogin2@password.de',
                'superuser_access' => '0',
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(1);
    }

    public function testGetUsersPlusRoleShouldIgnoreOffsetIfLimitIsNotSupplied()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->setCurrentUser('userLogin2', 'view', 1);

        $users = $this->api->getUsersPlusRole(1, $limit = null, $offset = 1);
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'userLogin2',
                'role'             => 'view',
                'capabilities'     => [],
                'email'            => 'userLogin2@password.de',
                'superuser_access' => '0',
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(1);
    }

    public function testGetUsersPlusRoleShouldNotAllowSuperuserFilterIfUserIsNotSuperUser()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->addUserWithAccess('userLogin3', 'superuser', 1);
        $this->setCurrentUser('userLogin2', 'view', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'superuser');
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'userLogin2',
                'role'             => 'view',
                'capabilities'     => [],
                'email'            => 'userLogin2@password.de',
                'superuser_access' => '0',
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(1);
    }

    public function testGetUsersPlusRoleShouldReturnAllUsersAndAccessIfUserHasAdminAccess()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'admin', 1);
        $this->addUserWithAccess('userLogin5', null, 1);
        $this->setCurrentUser('userLogin2', 'admin', 1);

        $users = $this->api->getUsersPlusRole(1);
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'userLogin2',
                'role'             => 'admin',
                'capabilities'     => [],
                'email'            => 'userLogin2@password.de',
                'superuser_access' => false,
            ],
            [
                'login'            => 'userLogin3',
                'role'             => 'view',
                'capabilities'     => [],
                'superuser_access' => false,
            ],
            [
                'login'            => 'userLogin4',
                'role'             => 'admin',
                'capabilities'     => [],
                'superuser_access' => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(3);
    }

    public function testGetUsersPlusRoleForAdminShouldLimitUsersToThoseWithAccessToSitesAsCurrentUsersAdminSites()
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
            [
                'login'            => 'userLogin2',
                'role'             => 'admin',
                'capabilities'     => [],
                'email'            => 'userLogin2@password.de',
                'superuser_access' => false,
            ],
            [
                'login'            => 'userLogin3',
                'role'             => 'view',
                'capabilities'     => [],
                'superuser_access' => false,
            ],
            [
                'login'            => 'userLogin4',
                'role'             => 'admin',
                'capabilities'     => [],
                'superuser_access' => false,
            ],
            [
                'login'            => 'userLogin5',
                'role'             => 'noaccess',
                'capabilities'     => [],
                'superuser_access' => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(4);
    }

    public function testGetUsersPlusRoleShouldReturnAllUsersAndAccessIfUserHasSuperuserAccess()
    {
        $this->addUserWithAccess('userLogin2', 'superuser', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', null, 1);
        $this->setCurrentUser('userLogin2', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1);
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'userLogin',
                'email'            => 'userlogin@password.de',
                'superuser_access' => false,
                'role'             => 'noaccess',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin2',
                'email'            => 'userLogin2@password.de',
                'superuser_access' => true,
                'role'             => 'superuser',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin3',
                'email'            => 'userLogin3@password.de',
                'superuser_access' => false,
                'role'             => 'view',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin4',
                'email'            => 'userLogin4@password.de',
                'superuser_access' => true,
                'role'             => 'superuser',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin5',
                'email'            => 'userLogin5@password.de',
                'superuser_access' => false,
                'role'             => 'noaccess',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(5);
    }

    public function testGetUsersPlusRoleShouldFilterUsersByAccessCorrectly()
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
            [
                'login'            => 'userLogin2',
                'role'             => 'admin',
                'capabilities'     => [],
                'email'            => 'userLogin2@password.de',
                'superuser_access' => false,
            ],
            [
                'login'            => 'userLogin5',
                'role'             => 'admin',
                'capabilities'     => [],
                'superuser_access' => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(2);

        // check new write role filtering works
        $users = $this->api->getUsersPlusRole(1, null, null, null, 'write');
        $this->cleanUsers($users);
        $expected = [
            ['login' => 'userLogin6', 'role' => 'write', 'capabilities' => [], 'superuser_access' => false],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(1);
    }

    public function testGetUsersPlusRoleShouldReturnUsersWithNoAccessCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'noaccess', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', 'noaccess', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'noaccess');
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'userLogin',
                'role'             => 'noaccess',
                'superuser_access' => false,
                'email'            => 'userlogin@password.de',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin2',
                'role'             => 'noaccess',
                'superuser_access' => false,
                'email'            => 'userLogin2@password.de',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin5',
                'role'             => 'noaccess',
                'superuser_access' => false,
                'email'            => 'userLogin5@password.de',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(3);
    }

    public function testGetUsersPlusRoleShouldSearchForSuperUsersCorrectly()
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
            [
                'login'            => 'userLogin2',
                'email'            => 'userLogin2@password.de',
                'superuser_access' => true,
                'role'             => 'superuser',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin4',
                'email'            => 'userLogin4@password.de',
                'superuser_access' => true,
                'role'             => 'superuser',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(2);
    }

    public function testGetUsersPlusRoleShouldSearchByTextCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'superuser', 1, 'someemail@email.com');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', 'superuser', 1, 'someemail2@email.com');
        $this->addUserWithAccess('userLogin4', null, 1);
        $this->setCurrentUser('searchTextLogin', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, 'searchText');
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'searchTextLogin',
                'email'            => 'someemail@email.com',
                'superuser_access' => true,
                'role'             => 'superuser',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin2',
                'email'            => 'searchTextdef@email.com',
                'superuser_access' => false,
                'role'             => 'view',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(2);
    }

    public function testGetUsersPlusRoleShouldApplyLimitAndOffsetCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'superuser', 1, 'someemail@email.com');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', 'superuser', 1, 'someemail2@email.com');
        $this->addUserWithAccess('userLogin4', null, 1);
        $this->setCurrentUser('searchTextLogin', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, $limit = 2, $offset = 1);
        $this->cleanUsers($users);
        $expected = [
            [
                'login'            => 'userLogin',
                'email'            => 'userlogin@password.de',
                'superuser_access' => false,
                'role'             => 'noaccess',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
            [
                'login'            => 'userLogin2',
                'email'            => 'searchTextdef@email.com',
                'superuser_access' => false,
                'role'             => 'view',
                'capabilities'     => [],
                'uses_2fa'         => false,
            ],
        ];
        self::assertEquals($expected, $users);
        self::assertResultCountHeader(5);
    }

    public function testGetSitesAccessForUserShouldReturnAccessForUser()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access   = $this->api->getSitesAccessForUser('userLogin');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'admin', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(3);
    }

    public function testGetUserCapabilitiesAfterFilter()
    {
        $this->addUserWithAccess('userLoginCapabilities', 'view', 1, 'searchTextdef@email.com');
        $this->api->addCapabilities('userLoginCapabilities', 'tagmanager_write', 1);

        $access = $this->api->getSitesAccessForUser('userLoginCapabilities', null, 1, null, 'view');

        self::assertEquals(['tagmanager_write'], $access[0]['capabilities']);
        self::assertResultCountHeader(1);
    }

    public function testGetSitesAccessForUserShouldIgnoreOffsetIfLimitNotSupplied()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access   = $this->api->getSitesAccessForUser('userLogin', $limit = null, $offset = 1);
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'admin', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(3);
    }

    public function testGetSitesAccessForUserShouldApplyLimitAndOffsetCorrectly()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access   = $this->api->getSitesAccessForUser('userLogin', $limit = 2, $offset = 1);
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(3);
    }

    public function testGetSitesAccessForUserShouldSearchSitesCorrectly()
    {
        Fixture::createWebsite('2010-01-02 00:00:00');

        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);
        $this->api->setUserAccess('userLogin', 'view', [4]);

        SitesManagerAPI::getInstance()->updateSite(1, 'searchTerm site');
        SitesManagerAPI::getInstance()->updateSite(2, null, ['http://searchTerm.com']);
        SitesManagerAPI::getInstance()->updateSite(
            3,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            'the searchTerm group'
        );

        $access   = $this->api->getSitesAccessForUser('userLogin', null, null, 'searchTerm');
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '1', 'site_name' => 'searchTerm site', 'role' => 'admin', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(3);
    }

    public function testGetSitesAccessForUserShouldFilterByAccessCorrectly()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $access   = $this->api->getSitesAccessForUser('userLogin', null, null, null, 'view');
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(2);
    }

    public function testGetSitesAccessForUserShouldLimitSitesIfUserIsAdmin()
    {
        $this->addUserWithAccess('userLogin2', 'view', [1, 2, 3], 'userlogin2@email.com');

        $this->api->setUserAccess('userLogin', 'admin', [1, 2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $this->setCurrentUser('userLogin', 'admin', [1, 2]);

        $access   = $this->api->getSitesAccessForUser('userLogin2', null, null, null, 'view');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(2);
    }

    public function testGetSitesAccessForUserShouldLimitSitesIfUserIsAdminAndStillSelectNoAccessSitesCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'view', [1], 'userlogin2@email.com');

        $this->api->setUserAccess('userLogin', 'admin', [1, 2, 3]);

        $this->setCurrentUser('userLogin', 'admin', [1, 2, 3]);

        $access   = $this->api->getSitesAccessForUser('userLogin2', null, null, null, 'noaccess');
        $expected = [
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(2);
    }

    public function testGetSitesAccessForUserShouldSelectSitesCorrectlyIfAtLeastViewRequested()
    {
        $this->addUserWithAccess('userLogin2', 'view', [1], 'userlogin2@email.com');
        $this->api->setUserAccess('userLogin2', 'admin', [2]);

        $access   = $this->api->getSitesAccessForUser('userLogin2', null, null, null, 'some');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'view', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'admin', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(2);
    }

    public function testGetSitesAccessForUserShouldReportIfUserHasNoAccessToSites()
    {
        $access   = $this->api->getSitesAccessForUser('userLogin');
        $expected = [
            ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
            ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
            ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'noaccess', 'capabilities' => []],
        ];
        self::assertEquals($expected, $access);

        // test when search returns empty result
        $this->api->setUserAccess('userLogin', 'view', 1);

        $access   = $this->api->getSitesAccessForUser('userLogin', null, null, 'asdklfjds');
        $expected = [];
        self::assertEquals($expected, $access);
        self::assertResultCountHeader(0);
    }

    public function testSetUserAccessMultipleRolesCannotBeSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionMultipleRoleSet');

        $this->api->setUserAccess($this->login, ['view', 'admin'], [1]);
    }

    public function testSetUserAccessNeedsAtLeastOneRole()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionNoRoleSet');

        $this->api->setUserAccess($this->login, [TestCap2::ID], [1]);
    }

    public function testSetUserAccessNeedsAtLeastOneRoleAsString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->setUserAccess($this->login, TestCap2::ID, [1]);
    }

    public function testSetUserAccessInvalidCapability()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->setUserAccess($this->login, ['admin', 'foobar'], [1]);
    }

    public function testSetUserAccessNeedsAtLeastOneRoleNoneGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionNoRoleSet');

        $this->api->setUserAccess($this->login, [], [1]);
    }

    public function testSetUserAccessCannotSetAdminToAnonymous()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAnonymousAccessNotPossible');

        $this->api->setUserAccess('anonymous', 'admin', [1]);
    }

    public function testSetUserAccessCannotSetWriteToAnonymous()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAnonymousAccessNotPossible');

        $this->api->setUserAccess('anonymous', 'write', [1]);
    }

    public function testSetUserAccessCannotSetViewToAnonymousWithoutPassword()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithPassword');

        $_GET['force_api_session'] = 1;
        try {
            $this->api->setUserAccess('anonymous', 'view', [1]);
        } finally {
            unset($_GET['force_api_session']);
        }
    }

    public function testSetUserAccessUserDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->api->setUserAccess('foobar', Admin::ID, [1]);
    }

    public function testSetUserAccessSetRoleAndCapabilities()
    {
        $access = [TestCap2::ID, View::ID, TestCap3::ID];
        $this->api->setUserAccess($this->login, $access, [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = [
            ['site' => '1', 'access' => 'view'],
            ['site' => '1', 'access' => TestCap2::ID],
            ['site' => '1', 'access' => TestCap3::ID],
        ];
        self::assertEquals($expected, $access);
    }

    public function testSetUserAccessSetRoleAsString()
    {
        $this->api->setUserAccess($this->login, View::ID, [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);
        self::assertEquals([['site' => '1', 'access' => 'view']], $access);
    }

    public function testSetUserAccessSetRoleAsArray()
    {
        $this->api->setUserAccess($this->login, [View::ID], [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);
        self::assertEquals([['site' => '1', 'access' => 'view']], $access);
    }

    public function testAddCapabilitiesFailsWhenNotCapabilityIsGivenAsString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->addCapabilities($this->login, View::ID, [1]);
    }

    public function testAddCapabilitiesFailsWhenNotCapabilityIsGivenAsArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->addCapabilities($this->login, [TestCap2::ID, View::ID], [1]);
    }

    public function testAddCapabilitiesFailsWhenUserDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->api->addCapabilities('foobar', [TestCap2::ID], [1]);
    }

    public function testAddCapabilitiesDoesNotAddSameCapabilityTwice()
    {
        $addAccess = [TestCap2::ID, View::ID, TestCap3::ID];
        $this->api->setUserAccess($this->login, $addAccess, [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = [
            ['site' => '1', 'access' => 'view'],
            ['site' => '1', 'access' => TestCap2::ID],
            ['site' => '1', 'access' => TestCap3::ID],
        ];
        self::assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, [TestCap2::ID, TestCap3::ID], [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);
        self::assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, [TestCap2::ID, TestCap1::ID, TestCap3::ID], [1]);

        $expected[] = ['site' => '1', 'access' => TestCap1::ID];
        $access     = $this->model->getSitesAccessFromUser($this->login);
        self::assertEquals($expected, $access);
    }

    public function testAddCapabilitiesDoesNotAddCapabilityToUserWithNoRole()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('UsersManager_ExceptionNoCapabilitiesWithoutRole');

        $access = $this->model->getSitesAccessFromUser($this->login);

        self::assertEquals([], $access);

        $this->api->addCapabilities($this->login, array(TestCap2::ID, TestCap3::ID), array(1));
    }

    public function testAddCapabilitiesDoesNotAddCapabilitiesWhichAreIncludedInRoleAlready()
    {
        $this->api->setUserAccess($this->login, Write::ID, [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = [
            ['site' => '1', 'access' => 'write'],
        ];
        self::assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, [TestCap2::ID, TestCap3::ID], [1]);

        $expected[] = ['site' => '1', 'access' => TestCap3::ID];
        $access     = $this->model->getSitesAccessFromUser($this->login);

        // did not add TestCap2
        self::assertEquals($expected, $access);
    }

    public function testAddCapabilitiesDoesAddCapabilitiesWhichAreNotIncludedInRoleYetAlready()
    {
        $this->api->setUserAccess($this->login, Admin::ID, [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);

        $expected = [
            ['site' => '1', 'access' => 'admin'],
        ];
        self::assertEquals($expected, $access);

        $this->api->addCapabilities($this->login, [TestCap2::ID, TestCap1::ID, TestCap3::ID], [1]);

        $access = $this->model->getSitesAccessFromUser($this->login);
        self::assertEquals($expected, $access);
    }

    public function testRemoveCapabilitiesFailsWhenNotCapabilityIsGivenAsString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->removeCapabilities($this->login, View::ID, [1]);
    }

    public function testRemoveCapabilitiesFailsWhenNotCapabilityIsGivenAsArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionAccessValues');

        $this->api->removeCapabilities($this->login, [TestCap2::ID, View::ID], [1]);
    }

    public function testRemoveCapabilitiesFailsWhenUserDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->api->removeCapabilities('foobar', [TestCap2::ID], [1]);
    }

    public function testRemoveCapabilities()
    {
        $addAccess = [View::ID, TestCap2::ID, TestCap3::ID, TestCap1::ID];
        $this->api->setUserAccess($this->login, $addAccess, [1]);

        $access = $this->getAccessInSite($this->login, 1);
        self::assertEquals($addAccess, $access);

        $this->api->removeCapabilities($this->login, [TestCap3::ID, TestCap2::ID], 1);

        $access = $this->getAccessInSite($this->login, 1);
        self::assertEquals([View::ID, TestCap1::ID], $access);
    }

    public function testSetSuperUserAccessFailsIfCurrentPasswordIsIncorrect()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_CurrentPasswordNotCorrect');

        $this->api->setSuperUserAccess($this->login, true, 'asldfkjds');
    }


    public function testInviteUserInitialIdSiteMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_AddUserNoInitialAccessError');
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin'    => "testInviteUser",
                'email'        => "testInviteUser@example.com",
                'expiryInDays' => 7,
            ]
        );
    }

    public function testInviteUserFailsWhenDomainNotAllowed()
    {
        $this->expectExceptionMessage('UsersManager_ErrorEmailDomainNotAllowed');

        $settings = StaticContainer::get(SystemSettings::class);
        $settings->allowedEmailDomains->setValue(['example.org', 'matomo.com', 'password.de']);

        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin' => 'foobar',
                'email' => 'foobar@matomo.org',
                'initialIdSite' => 1,
                'expiryInDays' => 7
            ]
        );
    }

    public function testInviteUserInitialIdSiteError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("An unexpected website was found in the request: website id was set to '10'");
        Request::processRequest(
            'UsersManager.inviteUser',
            [
                'userLogin'     => "testInviteUser",
                'email'         => "testInviteUser@example.com",
                'initialIdSite' => 10,
                'expiryInDays'  => 7,
            ]
        );
    }


    public function testInviteUserAsSuperUser()
    {
        $eventWasFired = false;
        $capturedMails = [];

        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        EventDispatcher::getInstance()->addObserver(
            'UsersManager.inviteUser.end',
            function ($userLogin, $email) use (&$eventWasFired) {
                self::assertEquals('pendingLoginTest', $userLogin);
                self::assertEquals('pendingLoginTest@matomo.org', $email);
                $eventWasFired = true;
            }
        );

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $isPending = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($isPending);
        self::assertTrue($eventWasFired);

        self::assertCount(2, $capturedMails);
        self::assertInstanceOf(UserCreatedEmail::class, $capturedMails[0]);
        self::assertInstanceOf(UserInviteEmail::class, $capturedMails[1]);
    }

    public function testChangingEmailOfInvitedUserShouldResendInvitation()
    {
        Fixture::createSuperUser();
        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $isPending = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($isPending);

        $eventWasFired = false;
        $capturedMails = [];

        Piwik::addAction('Mail.send', function (Mail $mail) use (&$capturedMails) {
            $capturedMails[] = $mail;
        });

        EventDispatcher::getInstance()->addObserver(
            'UsersManager.inviteUser.end',
            function ($userLogin, $email) use (&$eventWasFired) {
                $eventWasFired = true;
            }
        );

        $this->api->updateUser('pendingLoginTest', false, 'pendingLoginTest2@matomo.org', false, Fixture::ADMIN_USER_PASSWORD);
        self::assertFalse($eventWasFired); // event should not be fired on email change
        self::assertCount(1, $capturedMails);
        self::assertInstanceOf(UserInviteEmail::class, $capturedMails[0]);
    }

    public function testInviteUserAsAdmin()
    {
        $this->addUserWithAccess('adminUser', 'admin', 1);
        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);
    }

    public function testInviteUserAsAdminForAnotherSiteDoesntWork()
    {
        self::expectException(\Exception::class);

        $this->addUserWithAccess('adminUser', 'admin', 1);
        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 2);
    }

    public function testInviteUserAsWriteUserNotAllowed()
    {
        self::expectException(NoAccessException::class);

        $this->addUserWithAccess('anyUser', 'write', 1);
        $this->setCurrentUser('anyUser', 'write', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
    }

    public function testInviteUserAsUserNotAllowed()
    {
        self::expectException(NoAccessException::class);

        $this->addUserWithAccess('anyUser', 'view', 1);
        $this->setCurrentUser('anyUser', 'view', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
    }

    public function testInviteUserExpiredInGivenDays()
    {
        Date::$now   = time(); // freeze time, so it doesn't change between inviting user and comparing the time
        $expiredDays = 10;
        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1, $expiredDays);
        $user    = $this->model->getUser('pendingLoginTest');
        $expired = Date::factory($user['invite_expired_at'])->getTimestamp();
        $now     = Date::now()->getTimestamp();
        $diff    = $expired - $now;
        self::assertEquals($expiredDays, $diff / 3600 / 24);
    }

    public function testResendInviteAsSuperUser()
    {
        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);

        $eventWasFired = false;

        EventDispatcher::getInstance()->addObserver(
            'UsersManager.inviteUser.resendInvite',
            function ($userLogin) use (&$eventWasFired) {
                self::assertEquals('pendingLoginTest', $userLogin);
                $eventWasFired = true;
            }
        );

        $this->api->resendInvite('pendingLoginTest', true);
        self::assertTrue($eventWasFired);
    }

    public function testResendInviteFailsIfUserNotPending()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->api->resendInvite('notExistingUser');
    }

    public function testResendInviteAsInviterWithAdminAccess()
    {
        $this->addUserWithAccess('adminUser', 'admin', 1);
        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);

        $eventWasFired = false;

        EventDispatcher::getInstance()->addObserver(
            'UsersManager.inviteUser.resendInvite',
            function ($userLogin) use (&$eventWasFired) {
                self::assertEquals('pendingLoginTest', $userLogin);
                $eventWasFired = true;
            }
        );

        $this->api->resendInvite('pendingLoginTest', 1);
        self::assertTrue($eventWasFired);
    }

    public function testResendInviteFailsAsInviterWithoutAdminAccess()
    {
        self::expectException(NoAccessException::class);

        $this->addUserWithAccess('adminUser', 'write', 1);

        // fake admin access for inviting the user
        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);

        // degraded to write access
        $this->setCurrentUser('adminUser', 'admin', []);
        $this->setCurrentUser('adminUser', 'write', 1);

        $this->api->resendInvite('pendingLoginTest');
    }

    public function testResendInviteFailsAsNotInvitingAdmin()
    {
        self::expectException(NoAccessException::class);
        self::expectExceptionMessage('UsersManager_ExceptionResendInviteDenied');

        $this->addUserWithAccess('adminUser', 'admin', 1);
        $this->addUserWithAccess('anotherAdminUser', 'admin', 1);

        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);

        // another admin tries to resend invite
        $this->setCurrentUser('anotherAdminUser', 'admin', 1);

        $this->api->resendInvite('pendingLoginTest', 1);
    }

    public function testInvitedUserCanBeRemovedBySuperUser()
    {
        Fixture::createSuperUser();
        $this->addUserWithAccess('adminUser', 'admin', 1);
        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);

        $this->setCurrentUser('superUserLogin', 'superuser', 1);

        $this->api->deleteUser('pendingLoginTest');
        self::assertEmpty($this->model->getUser('pendingLoginTest'));
    }

    public function testInvitedUserCanBeRemovedByInviter()
    {
        Fixture::createSuperUser();
        $this->addUserWithAccess('adminUser', 'admin', 1);
        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);

        $this->api->deleteUser('pendingLoginTest');
        self::assertEmpty($this->model->getUser('pendingLoginTest'));
    }

    public function testInvitedUserCanNOTBeRemovedByOtherAdmin()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('UsersManager_ExceptionUserDoesNotExist');

        $this->addUserWithAccess('adminUser', 'admin', 1);
        $this->addUserWithAccess('adminUser2', 'admin', 1);
        $this->setCurrentUser('adminUser', 'admin', 1);

        $this->api->inviteUser('pendingLoginTest', 'pendingLoginTest@matomo.org', 1);
        $user = $this->model->isPendingUser('pendingLoginTest');
        self::assertTrue($user);

        $this->setCurrentUser('adminUser2', 'admin', 1);

        $this->api->deleteUser('pendingLoginTest');
    }

    private function getAccessInSite($login, $idSite)
    {
        $access = $this->model->getSitesAccessFromUser($login);
        $ids    = [];
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
        return [
            'Piwik\Access'                       => new FakeAccess(),
            'usersmanager.user_preference_names' => \Piwik\DI::add(
                [
                    'randomDoesNotExist',
                    'RandomNOTREQUESTED',
                    'preferenceName',
                ]
            ),
            'observers.global'                   => \Piwik\DI::add(
                [
                    [
                        'Access.Capability.addCapabilities',
                        \Piwik\DI::value(function (&$capabilities) {
                            $capabilities[] = new TestCap1();
                            $capabilities[] = new TestCap2();
                            $capabilities[] = new TestCap3();
                        }),
                    ],
                ]
            ),
        ];
    }

    private function addUserWithAccess($username, $accessLevel, $idSite, $email = null)
    {
        $this->api->addUser($username, 'password', $email ?: "$username@password.de");
        if ($accessLevel == 'superuser') {
            $userUpdater = new UserUpdater();
            $userUpdater->setSuperUserAccessWithoutCurrentPassword($username, true);
        } elseif ($accessLevel) {
            $this->api->setUserAccess($username, $accessLevel, $idSite);
        }
    }

    public function setCurrentUser($username, $accessLevel, $idSite)
    {
        FakeAccess::$identity  = $username;
        FakeAccess::$superUser = $accessLevel == 'superuser';
        if ($accessLevel == 'view') {
            FakeAccess::$idSitesView = is_array($idSite) ? $idSite : [$idSite];
        } elseif ($accessLevel == 'admin') {
            FakeAccess::$idSitesAdmin = is_array($idSite) ? $idSite : [$idSite];
        } elseif ($accessLevel == 'write') {
            FakeAccess::$idSitesWrite = is_array($idSite) ? $idSite : [$idSite];
        }
    }

    private static function assertResultCountHeader($expected)
    {
        self::assertArrayHasKey('X-Matomo-Total-Results', Common::$headersSentInTests, 'X-Matomo-Total-Results header not sent');
        self::assertEquals($expected, Common::$headersSentInTests['X-Matomo-Total-Results']);
    }

    private function cleanUsers(&$users)
    {
        foreach ($users as &$user) {
            unset($user['date_registered']);
            unset($user['invite_expired_at']);
            unset($user['invite_accept_at']);
            unset($user['invite_token']);
            unset($user['invite_link_token']);
            unset($user['invite_status']);
            unset($user['invited_by']);
        }
    }
}
