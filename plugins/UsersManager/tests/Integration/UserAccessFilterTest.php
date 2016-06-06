<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Access;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserAccessFilter;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestUserAccessFilter extends UserAccessFilter {

    public function isNonSuperUserAllowedToSeeThisLogin($login)
    {
        return parent::isNonSuperUserAllowedToSeeThisLogin($login);
    }
}

/**
 * @group UsersManager
 * @group UserAccessFilterTest
 * @group UserAccessFilter
 * @group Plugins
 */
class UserAccessFilterTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var Access
     */
    private $access;

    /**
     * @var TestUserAccessFilter
     */
    private $filter;

    private $users = array(
        'login2' => array('view' => array(1,3,5),   'admin' => array(2,6)),
        'login3' => array('view' => array(),        'admin' => array()), // no access to any site
        'login4' => array('view' => array(6),       'admin' => array()), // only access to one with view
        'login5' => array('view' => array(),        'admin' => array(3)), // only access to one with admin
        'login6' => array('view' => array(),        'admin' => array(6,3)), // access to a couple of sites with admin
        'login7' => array('view' => array(2,1,6,3), 'admin' => array()), // access to a couple of sites with view
        'login8' => array('view' => array(4,7),     'admin' => array(2,5)), // access to a couple of sites with admin and view
    );

    public function setUp()
    {
        parent::setUp();

        // set up your test here if needed
        $this->model  = new Model();
        $this->access = new FakeAccess();

        $this->createManyWebsites();
        $this->createManyUsers();
        FakeAccess::clearAccess();

        $this->filter = new TestUserAccessFilter($this->model, $this->access);
    }

    public function test_filterUser_WithSuperUserAccess_ShouldAlwaysReturnTrue()
    {
        $this->configureAcccessForLogin('login1');
        foreach ($this->getAllLogins() as $login) {
            $this->assertSame(array('login' => $login), $this->filter->filterUser(array('login' => $login)));
        }
    }

    public function test_filterUser_WithViewUserAccess_ShouldOnlyReturnUserForOwnLogin()
    {
        $identity = 'login4';
        $this->configureAcccessForLogin($identity);
        $this->assertSame(array('login' => $identity), $this->filter->filterUser(array('login' => $identity)));
        foreach ($this->getAllLogins() as $login) {
            if ($login !== $identity) {
                $this->assertNull($this->filter->filterUser(array('login' => $login)));
            }
        }
    }

    /**
     * @dataProvider getIsUserAllowedToSeeThisLoginWithAdminAccess
     */
    public function test_filterUser_WithAdminAccess_ShouldOnlyReturnUserForOwnLogin($expectedAllowed, $loginToSee)
    {
        $this->configureAcccessForLogin('login2');
        if ($expectedAllowed) {
            $this->assertSame(array('login' => $loginToSee), $this->filter->filterUser(array('login' => $loginToSee)));
        } else {
            $this->assertSame(null, $this->filter->filterUser(array('login' => $loginToSee)));
        }
    }

    /**
     * @dataProvider getIsUserAllowedToSeeThisLoginWithAdminAccess
     */
    public function test_isNonSuperUserAllowedToSeeThisLogin_WithAdminAccess_IsAllowedToSeeAnyUserHavingAccessToSameAdminSites($expectedAllowed, $loginToSee)
    {
        $this->configureAcccessForLogin('login2');
        $this->assertSame($expectedAllowed, $this->filter->isNonSuperUserAllowedToSeeThisLogin($loginToSee));
    }

    public function getIsUserAllowedToSeeThisLoginWithAdminAccess()
    {
        return array(
            array($expectedAllowed = false, 'login1'), // not allowed to see this user as it has super user access
            array($expectedAllowed = true,  'login2'), // it is the own user so visible anyway
            array($expectedAllowed = false, 'login3'), // not allowed to see this user as this one does not have access to any site
            array($expectedAllowed = true,  'login4'),
            array($expectedAllowed = false, 'login5'), // this user doesn't share any site id where the user has admin access
            array($expectedAllowed = true,  'login6'),
            array($expectedAllowed = true,  'login7'),
            array($expectedAllowed = true,  'login8'),
        );
    }

    public function test_isNonSuperUserAllowedToSeeThisLogin_WithAdminAccess_IsAllowedToSeeAnyUserHavingAccessToSameAdminSites_UserHasAccessToOnlyOneAdminSite()
    {
        $this->configureAcccessForLogin('login5');

        $this->assertTrue($this->filter->isNonSuperUserAllowedToSeeThisLogin('login2'));
        $this->assertTrue($this->filter->isNonSuperUserAllowedToSeeThisLogin('login5'));
        $this->assertTrue($this->filter->isNonSuperUserAllowedToSeeThisLogin('login7'));
        $this->assertTrue($this->filter->isNonSuperUserAllowedToSeeThisLogin('login6'));

        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login1')); // a user having view access only is not allowed to see any other user
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login3'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login4'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login8'));
    }

    public function test_isNonSuperUserAllowedToSeeThisLogin_WithOnlyViewAccess_IsAllowedToSeeOnlyOwnUser()
    {
        $this->configureAcccessForLogin('login7');
        $this->assertTrue($this->filter->isNonSuperUserAllowedToSeeThisLogin('login7')); // a view user is allowed to see itself

        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login1')); // a user having view access only is not allowed to see any other user
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login2'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login3'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login4'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login5'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login6'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login8'));
    }

    public function test_isNonSuperUserAllowedToSeeThisLogin_WithNoAccess_IsStillAllowedToSeeAnyUser()
    {
        $this->configureAcccessForLogin('login3');
        $this->assertTrue($this->filter->isNonSuperUserAllowedToSeeThisLogin('login3')); // a view user is allowed to see itself

        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login1'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login2'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login4'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login5'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login7'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login6'));
        $this->assertFalse($this->filter->isNonSuperUserAllowedToSeeThisLogin('login8'));
    }

    /**
     * @dataProvider getTestFilterLogins
     */
    public function test_filterLogins($expectedLogins, $loginIdentity, $logins)
    {
        $this->configureAcccessForLogin($loginIdentity);
        $this->assertSame($expectedLogins, $this->filter->filterLogins($logins)); // a view user is allowed to see itself
    }

    /**
     * @dataProvider getTestFilterLogins
     */
    public function test_filterUsers($expectedLogins, $loginIdentity, $logins)
    {
        $this->configureAcccessForLogin($loginIdentity);

        $users = array();
        $expectedUsers = array();

        foreach ($logins as $login) {
            $user = array('login' => $login, 'alias' => 'test', 'password' => md5('pass'));

            $users[] = $user;
            if (in_array($login, $expectedLogins)) {
                $expectedUsers[] = $user;
            }
        }

        $this->assertSame($expectedUsers, $this->filter->filterUsers($users)); // a view user is allowed to see itself
    }

    /**
     * @dataProvider getTestFilterLogins
     */
    public function test_filterLoginIndexedArray($expectedLogins, $loginIdentity, $logins)
    {
        $this->configureAcccessForLogin($loginIdentity);

        $testArray = array();
        $expectedTestArray = array();

        foreach ($logins as $login) {
            $anything = array('foo' . $login);

            $users[$login] = $anything;

            if (in_array($login, $expectedLogins)) {
                $expectedUsers[$login] = $anything;
            }
        }

        $this->assertSame($expectedTestArray, $this->filter->filterLoginIndexedArray($testArray)); // a view user is allowed to see itself
    }

    public function getTestFilterLogins()
    {
        return array(
            array($expectedLogins = $this->getAllLogins(),                $identity = 'login1', $this->getAllLogins()), // a super user is allowed to see all logins
            array($expectedLogins = array('login2', 'foobar'),            $identity = 'login1', array('login2', 'foobar')), // for super users we do not even check if they actually exist
            array($expectedLogins = $this->buildLogins(array(2,4)),       $identity = 'login2', array('login2', 'foobar', 'login4', 'login3')), // should remove logins that do not actually exist when user has admin permission
            array($expectedLogins = $this->buildLogins(array(2,4,6,7,8)), $identity = 'login2', $this->getAllLogins()), // an admin user can see users having access to the admin sites
            array($expectedLogins = $this->buildLogins(array(3)),         $identity = 'login3', $this->getAllLogins()), // a user with no access to any site can only see itself
            array($expectedLogins = array('foobar'),                      $identity = 'foobar', array('foobar')), // doesn't check whether user exists when not having access to any site and user doesn't actually exist
            array($expectedLogins = $this->buildLogins(array(4)),         $identity = 'login4', $this->getAllLogins()), // a user with only view access to a site can only see itself
            array($expectedLogins = $this->buildLogins(array(2,5,6,7)),   $identity = 'login5', $this->getAllLogins()), // has access to one admin site
            array($expectedLogins = $this->buildLogins(array(2,4,5,6,7)), $identity = 'login6', $this->getAllLogins()), // has access to multiple admin sites
            array($expectedLogins = $this->buildLogins(array(7)),         $identity = 'login7', $this->getAllLogins()), // has only access to multiple view sites
            array($expectedLogins = $this->buildLogins(array(2,7,8)),     $identity = 'login8', $this->getAllLogins()), // a user with only view access to a site can only see itself
            array($expectedLogins = array(),                              $identity = 'login1', array()), // no users given, should return empty array for user with super user access
            array($expectedLogins = array(),                              $identity = 'login2', array()), // no users given, should return empty array for user with admin access
            array($expectedLogins = array(),                              $identity = 'login3', array()), // no users given, should return empty array for user with no access
            array($expectedLogins = array(),                              $identity = 'login4', array()), // no users given, should return empty array for user with only view access
            array($expectedLogins = array('anonymous'),                   $identity = 'anonymous', array('anonymous')), // anonymous user can see itself
        );
    }

    public function test_getAllLogins_shouldBeUpToDate()
    {
        $this->assertSame($this->model->getUsersLogin(), $this->getAllLogins());
        $this->assertNotEmpty($this->getAllLogins());
    }

    public function test_buildLogins()
    {
        $this->assertSame(array('login2', 'login3', 'login7'), $this->buildLogins(array(2,3,7)));
        $this->assertSame(array(), $this->buildLogins(array()));
    }

    private function createManyWebsites()
    {
        for ($i = 0; $i < 10; $i++) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    private function buildLogins($ids)
    {
        $logins = array();
        foreach ($ids as $id) {
            $logins[] = 'login' . $id;
        }
        return $logins;
    }

    private function getAllLogins()
    {
        $logins = $this->buildLogins(range(1,8));
        array_unshift($logins, 'anonymous');
        return $logins;
    }

    private function createManyUsers()
    {
        $this->model->addUser('login1', md5('pass'), 'email1@example.com', 'alias1', md5('token1'), '2008-01-01 00:00:00');
        $this->model->addUser('login2', md5('pass'), 'email2@example.com', 'alias2', md5('token2'), '2008-01-01 00:00:00');
        // login3 won't have access to any site
        $this->model->addUser('login3', md5('pass'), 'email3@example.com', 'alias3', md5('token3'), '2008-01-01 00:00:00');
        $this->model->addUser('login4', md5('pass'), 'email4@example.com', 'alias4', md5('token4'), '2008-01-01 00:00:00');
        $this->model->addUser('login5', md5('pass'), 'email5@example.com', 'alias5', md5('token5'), '2008-01-01 00:00:00');
        $this->model->addUser('login6', md5('pass'), 'email6@example.com', 'alias6', md5('token6'), '2008-01-01 00:00:00');
        $this->model->addUser('login7', md5('pass'), 'email7@example.com', 'alias7', md5('token7'), '2008-01-01 00:00:00');
        $this->model->addUser('login8', md5('pass'), 'email8@example.com', 'alias8', md5('token8'), '2008-01-01 00:00:00');
        $this->model->addUser('anonymous', '',       'ano@example.com',   'anonymous', 'anonymous', '2008-01-01 00:00:00');

        $this->model->setSuperUserAccess('login1', true); // we treat this one as our superuser

        foreach ($this->users as $login => $permissions) {
            foreach ($permissions as $access => $idSites) {
                $this->model->addUserAccess($login, $access, $idSites);
            }
        }
    }

    private function configureAcccessForLogin($login)
    {
        $hasSuperUser = false;
        $idSitesAdmin = array();
        $idSitesView  = array();

        if ($login === 'login1') {
            $hasSuperUser = true;
        } elseif (isset($this->users[$login])) {
            $idSitesAdmin = $this->users[$login]['admin'];
            $idSitesView  = $this->users[$login]['view'];
        }

        FakeAccess::clearAccess($hasSuperUser, $idSitesAdmin, $idSitesView, $login);
    }

}
