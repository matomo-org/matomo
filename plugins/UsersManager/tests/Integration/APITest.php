<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests;

use Piwik\Auth\Password;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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
    
    private $login = 'userLogin';

    public function setUp()
    {
        parent::setUp();

        $this->api = API::getInstance();

        FakeAccess::clearAccess();
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        $this->api->addUser($this->login, 'password', 'userlogin@password.de');
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

    public function test_setUserAccess_ShouldSetAccessForMultipleUsers()
    {
        $this->api->addUser('userLogin2', 'password', 'userlogin2@password.de');
        $this->api->addUser('userLogin3', 'password', 'userlogin3@password.de');
        $this->api->addUser('userLogin4', 'password', 'userlogin4@password.de');

        $this->api->setUserAccess(['userLogin2', 'userLogin3'], 'view', [1, 2]);

        $expected = [
            ['site' => 1, 'access' => 'view'],
            ['site' => 2, 'access' => 'view'],
        ];
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin2'));
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin3'));
        $this->assertEquals([], $this->api->getSitesAccessFromUser('userLogin4'));
    }

    public function test_setUserAccess_ShouldOnlyAddAccess_IfIgnoreExistingSitesSet()
    {
        $this->api->addUser('userLogin2', 'password', 'userlogin2@password.de');

        $this->api->setUserAccess('userLogin2', 'view', [1]);
        $expected = [
            ['site' => 1, 'access' => 'view'],
        ];
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin2'));

        $this->api->setUserAccess('userLogin2', 'admin', 'all', true);
        $expected = [
            ['site' => 1, 'access' => 'view'],
            ['site' => 2, 'access' => 'admin'],
            ['site' => 3, 'access' => 'admin'],
        ];
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin2'));
    }

    public function test_setUserAccessMatching_shouldSearchByTextCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'view', 1, 'someemail@email.com', 'alias');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', null, 1, 'someemail2@email.com', 'alias-searchTextABC');
        $this->addUserWithAccess('userLogin4', null, 1);

        $this->api->setUserAccessMatching(1, 'admin', 'searchText');

        $expected = [
            ['site' => 1, 'access' => 'admin'],
        ];
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('searchTextLogin'));
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin2'));
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin3'));
        $this->assertEquals([], $this->api->getSitesAccessFromUser('userLogin4'));
    }

    public function test_setUserAccessMatching_shouldSearchByAccessCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->addUserWithAccess('userLogin3', 'admin', 1);
        $this->addUserWithAccess('userLogin4', 'admin', 1);
        $this->addUserWithAccess('userLogin5', null, 1);

        $this->api->setUserAccessMatching(1, 'view', null, 'admin'); // set all admin users to view

        $expected = [
            ['site' => 1, 'access' => 'view'],
        ];
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin2'));
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin3'));
        $this->assertEquals($expected, $this->api->getSitesAccessFromUser('userLogin4'));
        $this->assertEquals([], $this->api->getSitesAccessFromUser('userLogin5'));
    }

    public function test_getAllUsersPreferences_isEmpty_whenNoPreference()
    {
        $preferences = $this->api->getAllUsersPreferences(array('preferenceName'));
        $this->assertEmpty($preferences);
    }

    public function test_getAllUsersPreferences_isEmpty_whenNoPreferenceAndMultipleRequested()
    {
        $preferences = $this->api->getAllUsersPreferences(array('preferenceName', 'otherOne'));
        $this->assertEmpty($preferences);
    }

    public function test_getUserPreference_ShouldReturnADefaultPreference_IfNoneIsSet()
    {
        $siteId = $this->api->getUserPreference($this->login, API::PREFERENCE_DEFAULT_REPORT);
        $this->assertEquals('1', $siteId);
    }

    public function test_getUserPreference_ShouldReturnASetreference_IfNoneIsSet()
    {
        $this->api->setUserPreference($this->login, API::PREFERENCE_DEFAULT_REPORT, 5);

        $siteId = $this->api->getUserPreference($this->login, API::PREFERENCE_DEFAULT_REPORT);
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
        $this->api->setUserPreference($user2, 'myPreferenceName', 'valueForUser2');
        $this->api->setUserPreference($user2, 'RandomNOTREQUESTED', 'RandomNOTREQUESTED');

        $this->api->addUser($user3, 'password', 'userlogin3@password.de');
        $this->api->setUserPreference($user3, 'myPreferenceName', 'valueForUser3');
        $this->api->setUserPreference($user3, 'otherPreferenceHere', 'otherPreferenceVALUE');
        $this->api->setUserPreference($user3, 'RandomNOTREQUESTED', 'RandomNOTREQUESTED');

        $expected = array(
            $user2 => array(
                'myPreferenceName' => 'valueForUser2'
            ),
            $user3 => array(
                'myPreferenceName' => 'valueForUser3',
                'otherPreferenceHere' => 'otherPreferenceVALUE',
            ),
        );
        $result = $this->api->getAllUsersPreferences(array('myPreferenceName', 'otherPreferenceHere', 'randomDoesNotExist'));

        $this->assertSame($expected, $result);
    }

    public function test_getAllUsersPreferences_whenLoginContainsUnderscore()
    {
        $user2 = 'user_Login2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');
        $this->api->setUserPreference($user2, 'myPreferenceName', 'valueForUser2');
        $this->api->setUserPreference($user2, 'RandomNOTREQUESTED', 'RandomNOTREQUESTED');

        $expected = array(
            $user2 => array(
                'myPreferenceName' => 'valueForUser2'
            ),
        );
        $result = $this->api->getAllUsersPreferences(array('myPreferenceName', 'otherPreferenceHere', 'randomDoesNotExist'));

        $this->assertSame($expected, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function test_setUserPreference_throws_whenPreferenceNameContainsUnderscore()
    {
        $user2 = 'userLogin2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');
        $this->api->setUserPreference($user2, 'ohOH_myPreferenceName', 'valueForUser2');
    }

    public function test_updateUser()
    {
        $this->api->updateUser($this->login, 'newPassword', 'email@example.com', 'newAlias', false);

        $user = $this->api->getUser($this->login);

        $this->assertSame('email@example.com', $user['email']);
        $this->assertSame('newAlias', $user['alias']);

        $passwordHelper = new Password();

        $this->assertTrue($passwordHelper->verify(UsersManager::getPasswordHash('newPassword'), $user['password']));
    }

    public function test_getSitesAccessFromUser_forSuperUser()
    {
        $user2 = 'userLogin2';
        $this->api->addUser($user2, 'password', 'userlogin2@password.de');

        // new user doesn't have access to anything
        $access = $this->api->getSitesAccessFromUser($user2);
        $this->assertEmpty($access);

        $this->api->setSuperUserAccess($user2, true);

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
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 1,
            'results' => [
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'role' => 'view'],
            ],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldNotAllowSuperuserFilter_ifUserIsNotSuperUser()
    {
        $this->addUserWithAccess('userLogin2', 'view', 1);
        $this->addUserWithAccess('userLogin3', 'superuser', 1);
        $this->setCurrentUser('userLogin2', 'view', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'superuser');
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 1,
            'results' => [
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'role' => 'view'],
            ],
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
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 5,
            'results' => [
                ['login' => 'userLogin', 'alias' => 'userLogin', 'role' => 'noaccess'],
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'role' => 'admin'],
                ['login' => 'userLogin3', 'alias' => 'userLogin3', 'role' => 'view'],
                ['login' => 'userLogin4', 'alias' => 'userLogin4', 'role' => 'admin'],
                ['login' => 'userLogin5', 'alias' => 'userLogin5', 'role' => 'noaccess'],
            ],
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
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 5,
            'results' => [
                ['login' => 'userLogin', 'alias' => 'userLogin', 'email' => 'userlogin@password.de', 'superuser_access' => false, 'role' => 'noaccess'],
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'email' => 'userLogin2@password.de', 'superuser_access' => true, 'role' => 'superuser'],
                ['login' => 'userLogin3', 'alias' => 'userLogin3', 'email' => 'userLogin3@password.de', 'superuser_access' => false, 'role' => 'view'],
                ['login' => 'userLogin4', 'alias' => 'userLogin4', 'email' => 'userLogin4@password.de', 'superuser_access' => true, 'role' => 'superuser'],
                ['login' => 'userLogin5', 'alias' => 'userLogin5', 'email' => 'userLogin5@password.de', 'superuser_access' => false, 'role' => 'noaccess'],
            ],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldFilterUsersByAccessCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', 'admin', 1);
        $this->setCurrentUser('userLogin2', 'admin', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'admin');
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 2,
            'results' => [
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'role' => 'admin'],
                ['login' => 'userLogin5', 'alias' => 'userLogin5', 'role' => 'admin'],
            ],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldSearchForSuperUsersCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->api->setSuperUserAccess('userLogin2', true);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', null, 1);
        $this->setCurrentUser('userLogin2', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, null, 'superuser');
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 2,
            'results' => [
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'email' => 'userLogin2@password.de', 'superuser_access' => true, 'role' => 'superuser'],
                ['login' => 'userLogin4', 'alias' => 'userLogin4', 'email' => 'userLogin4@password.de', 'superuser_access' => true, 'role' => 'superuser'],
            ],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldSearchByTextCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'superuser', 1, 'someemail@email.com', 'alias');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', 'superuser', 1, 'someemail2@email.com', 'alias-searchTextABC');
        $this->addUserWithAccess('userLogin4', null, 1);
        $this->setCurrentUser('searchTextLogin', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, null, null, 'searchText');
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 3,
            'results' => [
                ['login' => 'searchTextLogin', 'alias' => 'alias', 'email' => 'someemail@email.com', 'superuser_access' => true, 'role' => 'superuser'],
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'email' => 'searchTextdef@email.com', 'superuser_access' => false, 'role' => 'view'],
                ['login' => 'userLogin3', 'alias' => 'alias-searchTextABC', 'email' => 'someemail2@email.com', 'superuser_access' => true, 'role' => 'superuser'],
            ],
        ];
        $this->assertEquals($expected, $users);
    }

    public function test_getUsersPlusRole_shouldApplyLimitAndOffsetCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'superuser', 1, 'someemail@email.com');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', 'superuser', 1, 'someemail2@email.com', 'alias-searchTextABC');
        $this->addUserWithAccess('userLogin4', null, 1);
        $this->setCurrentUser('searchTextLogin', 'superuser', 1);

        $users = $this->api->getUsersPlusRole(1, $limit = 2, $offset = 1);
        $this->cleanUsers($users['results']);
        $expected = [
            'total' => 5,
            'results' => [
                ['login' => 'userLogin', 'alias' => 'userLogin', 'email' => 'userlogin@password.de', 'superuser_access' => false, 'role' => 'noaccess'],
                ['login' => 'userLogin2', 'alias' => 'userLogin2', 'email' => 'searchTextdef@email.com', 'superuser_access' => false, 'role' => 'view'],
            ],
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
            'total' => '3',
            'results' => [
                ['idsite' => '1', 'site_name' => 'Piwik test', 'role' => 'admin'],
                ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view'],
                ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view'],
            ],
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
            'total' => '3',
            'results' => [
                ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view'],
                ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view'],
            ],
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
            'total' => '3',
            'results' => [
                ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view'],
                ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view'],
                ['idsite' => '1', 'site_name' => 'searchTerm site', 'role' => 'admin'],
            ],
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
            'total' => '2',
            'results' => [
                ['idsite' => '2', 'site_name' => 'Piwik test', 'role' => 'view'],
                ['idsite' => '3', 'site_name' => 'Piwik test', 'role' => 'view'],
            ],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_setSiteAccessMatching_shouldSetAllSitesIfNoFiltersUsed()
    {
        $this->api->setUserAccess('userLogin', 'view', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $this->api->setSiteAccessMatching('userLogin', 'admin');

        $access = $this->api->getSitesAccessFromUser('userLogin');
        $expected = [
            ['site' => '1', 'access' => 'admin'],
            ['site' => '2', 'access' => 'admin'],
            ['site' => '3', 'access' => 'admin'],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_setSiteAccessMatching_shouldFilterBySiteNameCorrectly()
    {
        Fixture::createWebsite('2010-01-02 00:00:00');

        $this->api->setUserAccess('userLogin', 'view', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);
        $this->api->setUserAccess('userLogin', 'view', [4]);

        SitesManagerAPI::getInstance()->updateSite(1, 'searchTerm site');
        SitesManagerAPI::getInstance()->updateSite(2, null, ['http://searchTerm.com']);
        SitesManagerAPI::getInstance()->updateSite(3, null, null, null, null, null, null, null, null, null, null, 'the searchTerm group');

        $this->api->setSiteAccessMatching('userLogin', 'admin', 'searchTerm');

        $access = $this->api->getSitesAccessFromUser('userLogin');
        $expected = [
            ['site' => '1', 'access' => 'admin'],
            ['site' => '2', 'access' => 'admin'],
            ['site' => '3', 'access' => 'admin'],
            ['site' => '4', 'access' => 'view'],
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_setSiteAccessMatching_shouldFilterByAccessCorrectly()
    {
        $this->api->setUserAccess('userLogin', 'admin', [1]);
        $this->api->setUserAccess('userLogin', 'view', [2]);
        $this->api->setUserAccess('userLogin', 'view', [3]);

        $this->api->setSiteAccessMatching('userLogin', 'admin', null, 'view');

        $access = $this->api->getSitesAccessFromUser('userLogin');
        $expected = [
            ['site' => '1', 'access' => 'admin'],
            ['site' => '2', 'access' => 'admin'],
            ['site' => '3', 'access' => 'admin']
        ];
        $this->assertEquals($expected, $access);
    }

    public function test_deleteUsersMatching_shouldSearchByTextCorrectly()
    {
        $this->addUserWithAccess('searchTextLogin', 'superuser', 1, 'someemail@email.com', 'alias');
        $this->addUserWithAccess('userLogin2', 'view', 1, 'searchTextdef@email.com');
        $this->addUserWithAccess('userLogin3', null, 1, 'someemail2@email.com', 'alias-searchTextABC');
        $this->addUserWithAccess('userLogin4', 'superuser', 1);

        $this->api->deleteUsersMatching(1, 'searchText');
        $this->assertFalse($this->api->userExists('searchTextLogin'));
        $this->assertFalse($this->api->userExists('userLogin2'));
        $this->assertFalse($this->api->userExists('userLogin3'));
        $this->assertTrue($this->api->userExists('userLogin4'));
    }

    public function test_deleteUsersMatching_shouldSearchByAccessCorrectly()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', 'admin', 1);

        $this->api->deleteUsersMatching(1, null, 'admin');
        $this->assertFalse($this->api->userExists('userLogin2'));
        $this->assertFalse($this->api->userExists('userLogin5'));
        $this->assertTrue($this->api->userExists('userLogin3'));
        $this->assertTrue($this->api->userExists('userLogin4'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage UsersManager_ExceptionDeleteOnlyUserWithSuperUserAccess UsersManager_ExceptionYouMustGrantSuperUserAccessFirst
     */
    public function test_deleteUsersMatching_shouldNotAllowDeletingAllSuperusers()
    {
        $this->addUserWithAccess('userLogin2', 'admin', 1);
        $this->addUserWithAccess('userLogin3', 'view', 1);
        $this->addUserWithAccess('userLogin4', 'superuser', 1);
        $this->addUserWithAccess('userLogin5', 'admin', 1);

        $this->api->deleteUsersMatching(1, 'userLogin');
    }

    private function getPreferenceId($preferenceName)
    {
        return $this->login . '_' . $preferenceName;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }

    private function addUserWithAccess($username, $accessLevel, $idSite, $email = null, $alias = null)
    {
        $this->api->addUser($username, 'password', $email ?: "$username@password.de", $alias);
        if ($accessLevel == 'superuser') {
            $this->api->setSuperUserAccess($username, true);
        } else if ($accessLevel) {
            $this->api->setUserAccess($username, $accessLevel, $idSite);
        }
    }

    public function setCurrentUser($username, $accessLevel, $idSite)
    {
        FakeAccess::$identity = $username;
        FakeAccess::$superUser = $accessLevel == 'superuser';
        if ($accessLevel == 'view') {
            FakeAccess::$idSitesView = [$idSite];
        } else if ($accessLevel == 'admin') {
            FakeAccess::$idSitesAdmin = [$idSite];
        }
    }

    private function cleanUsers(&$users)
    {
        foreach ($users as &$user) {
            unset($user['date_registered']);
        }
    }
}
