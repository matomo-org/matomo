<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Access;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserAccessFilter;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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
     * @var UserAccessFilter
     */
    private $filter;

    /**
     * @var \ReflectionMethod
     */
    private $isNonSuperUserAllowedToSeeThisLogin;

    private static $users = [
        'login2' => ['view' => [1,3,5],   'write' => [],    'admin' => [2,6]],
        'login3' => ['view' => [],        'write' => [],    'admin' => []], // no access to any site
        'login4' => ['view' => [6],       'write' => [],    'admin' => []], // only access to one with view
        'login5' => ['view' => [],        'write' => [],    'admin' => [3]], // only access to one with admin
        'login6' => ['view' => [],        'write' => [],    'admin' => [6,3]], // access to a couple of sites with admin
        'login7' => ['view' => [2,1,6,3], 'write' => [],    'admin' => []], // access to a couple of sites with view
        'login8' => ['view' => [4,7],     'write' => [],    'admin' => [2,5]], // access to a couple of sites with admin and view
        'login9' => ['view' => [],        'write' => [2,5], 'admin' => []], // access to a couple of sites with write
        'login10' => ['view' => [1,3],    'write' => [6],   'admin' => []], // access to a couple of sites with write and view
    ];

    public function setUp(): void
    {
        parent::setUp();

        // set up your test here if needed
        $this->model  = new Model();
        $this->access = new FakeAccess();

        FakeAccess::clearAccess();

        $this->filter = new UserAccessFilter($this->model, $this->access);
        $method = new \ReflectionMethod($this->filter, 'isNonSuperUserAllowedToSeeThisLogin');
        $method->setAccessible(true);
        $this->isNonSuperUserAllowedToSeeThisLogin = $method;
    }

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        self::createManyWebsites();
        self::createManyUsers();
    }

    public function testFilterUserWithSuperUserAccessShouldAlwaysReturnTrue()
    {
        $this->configureAccessForLogin('login1');
        foreach ($this->getAllLogins() as $login) {
            $this->assertSame(['login' => $login], $this->filter->filterUser(['login' => $login]));
        }
    }

    public function testFilterUserWithViewUserAccessShouldOnlyReturnUserForOwnLogin()
    {
        $identity = 'login4';
        $this->configureAccessForLogin($identity);
        $this->assertSame(['login' => $identity], $this->filter->filterUser(['login' => $identity]));
        foreach ($this->getAllLogins() as $login) {
            if ($login !== $identity) {
                $this->assertNull($this->filter->filterUser(['login' => $login]));
            }
        }
    }

    /**
     * @dataProvider getIsUserAllowedToSeeThisLoginWithAdminAccess
     */
    public function testFilterUserWithAdminAccessShouldOnlyReturnUserForOwnLogin($expectedAllowed, $loginToSee)
    {
        $this->configureAccessForLogin('login2');
        if ($expectedAllowed) {
            $this->assertSame(['login' => $loginToSee], $this->filter->filterUser(['login' => $loginToSee]));
        } else {
            $this->assertSame(null, $this->filter->filterUser(['login' => $loginToSee]));
        }
    }

    /**
     * @dataProvider getIsUserAllowedToSeeThisLoginWithAdminAccess
     */
    public function testIsNonSuperUserAllowedToSeeThisLoginWithAdminAccessIsAllowedToSeeAnyUserHavingAccessToSameAdminSites($expectedAllowed, $loginToSee)
    {
        $this->configureAccessForLogin('login2');
        $this->assertSame($expectedAllowed, $this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, $loginToSee));
    }

    public function getIsUserAllowedToSeeThisLoginWithAdminAccess()
    {
        return array(
            array($expectedAllowed = false, 'login1'), // not allowed to see this user as it has super user access
            array($expectedAllowed = true,  'login10'),
            array($expectedAllowed = true,  'login2'), // it is the own user so visible anyway
            array($expectedAllowed = false, 'login3'), // not allowed to see this user as this one does not have access to any site
            array($expectedAllowed = true,  'login4'),
            array($expectedAllowed = false, 'login5'), // this user doesn't share any site id where the user has admin access
            array($expectedAllowed = true,  'login6'),
            array($expectedAllowed = true,  'login7'),
            array($expectedAllowed = true,  'login8'),
            array($expectedAllowed = true,  'login9'),
        );
    }

    public function testIsNonSuperUserAllowedToSeeThisLoginWithAdminAccessIsAllowedToSeeAnyUserHavingAccessToSameAdminSitesUserHasAccessToOnlyOneAdminSite()
    {
        $this->configureAccessForLogin('login5');

        $this->assertTrue($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login2'));
        $this->assertTrue($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login5'));
        $this->assertTrue($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login7'));
        $this->assertTrue($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login6'));
        $this->assertTrue($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login10'));

        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login1'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login3'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login4'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login8'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login9'));
    }

    public function testIsNonSuperUserWithOnlyViewAccessAllowedToSeeOnlyOwnUser()
    {
        $this->configureAccessForLogin('login7');
        $this->assertTrue($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login7'));

        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login1'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login2'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login3'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login4'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login5'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login6'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login8'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login9'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login10'));
    }

    public function testIsNonSuperUserWithoutAnyAccessAllowedToSeeOnlyOwnUser()
    {
        $this->configureAccessForLogin('login3');
        $this->assertTrue($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login3'));

        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login1'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login2'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login4'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login5'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login7'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login6'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login8'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login9'));
        $this->assertFalse($this->isNonSuperUserAllowedToSeeThisLogin->invoke($this->filter, 'login10'));
    }

    /**
     * @dataProvider getTestFilterLogins
     */
    public function testFilterLogins($expectedLogins, $loginIdentity, $logins)
    {
        $this->configureAccessForLogin($loginIdentity);
        $this->assertSame($expectedLogins, $this->filter->filterLogins($logins));
    }

    /**
     * @dataProvider getTestFilterLogins
     */
    public function testFilterUsers($expectedLogins, $loginIdentity, $logins)
    {
        $this->configureAccessForLogin($loginIdentity);

        $users = [];
        $expectedUsers = [];

        foreach ($logins as $login) {
            $user = ['login' => $login, 'password' => md5('pass')];

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
    public function testFilterLoginIndexedArray($expectedLogins, $loginIdentity, $logins)
    {
        $this->configureAccessForLogin($loginIdentity);

        $testArray = [];
        $expectedTestArray = [];

        foreach ($logins as $login) {
            $anything = ['foo' . $login];

            $testArray[$login] = $anything;

            if (in_array($login, $expectedLogins)) {
                $expectedTestArray[$login] = $anything;
            }
        }

        $this->assertSame($expectedTestArray, $this->filter->filterLoginIndexedArray($testArray));
    }

    public function getTestFilterLogins()
    {
        return [
            [$expectedLogins = $this->getAllLogins(),                $identity = 'login1', $this->getAllLogins()], // a super user is allowed to see all logins
            [$expectedLogins = ['login2', 'foobar'],                 $identity = 'login1', ['login2', 'foobar']], // for super users we do not even check if they actually exist
            [$expectedLogins = $this->buildLogins([2,4]),            $identity = 'login2', ['login2', 'foobar', 'login4', 'login3']], // should remove logins that do not actually exist when user has admin permission
            [$expectedLogins = $this->buildLogins([10,2,4,6,7,8,9]), $identity = 'login2', $this->getAllLogins()], // an admin user can see users having access to the admin sites
            [$expectedLogins = $this->buildLogins([3]),              $identity = 'login3', $this->getAllLogins()], // a user with no access to any site can only see itself
            [$expectedLogins = ['foobar'],                           $identity = 'foobar', ['foobar']], // doesn't check whether user exists when not having access to any site and user doesn't actually exist
            [$expectedLogins = $this->buildLogins([4]),              $identity = 'login4', $this->getAllLogins()], // a user with only view access to a site can only see itself
            [$expectedLogins = $this->buildLogins([10,2,5,6,7]),     $identity = 'login5', $this->getAllLogins()], // has access to one admin site
            [$expectedLogins = $this->buildLogins([10,2,4,5,6,7]),   $identity = 'login6', $this->getAllLogins()], // has access to multiple admin sites
            [$expectedLogins = $this->buildLogins([7]),              $identity = 'login7', $this->getAllLogins()], // has only access to multiple view sites
            [$expectedLogins = $this->buildLogins([2,7,8,9]),        $identity = 'login8', $this->getAllLogins()], // has access to multiple view & admin sites
            [$expectedLogins = $this->buildLogins([9]),              $identity = 'login9', $this->getAllLogins()], // a user with write access only can only see itself
            [$expectedLogins = $this->buildLogins([10]),             $identity = 'login10', $this->getAllLogins()], // a user with view and write access to a site can only see itself
            [$expectedLogins = [],                                   $identity = 'login1', []], // no users given, should return empty array for user with super user access
            [$expectedLogins = [],                                   $identity = 'login2', []], // no users given, should return empty array for user with admin access
            [$expectedLogins = [],                                   $identity = 'login9', []], // no users given, should return empty array for user with write access
            [$expectedLogins = [],                                   $identity = 'login3', []], // no users given, should return empty array for user with no access
            [$expectedLogins = [],                                   $identity = 'login4', []], // no users given, should return empty array for user with only view access
            [$expectedLogins = ['anonymous'],                        $identity = 'anonymous', ['anonymous']], // anonymous user can see itself
        ];
    }

    public function testGetAllLoginsShouldBeUpToDate()
    {
        $this->assertSame($this->model->getUsersLogin(), $this->getAllLogins());
        $this->assertNotEmpty($this->getAllLogins());
    }

    public function testBuildLogins()
    {
        $this->assertSame(['login2', 'login3', 'login7'], $this->buildLogins([2,3,7]));
        $this->assertSame([], $this->buildLogins([]));
    }

    private static function createManyWebsites()
    {
        for ($i = 0; $i < 10; $i++) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    private function buildLogins($ids)
    {
        $logins = [];
        foreach ($ids as $id) {
            $logins[] = 'login' . $id;
        }
        return $logins;
    }

    private function getAllLogins()
    {
        $logins = $this->buildLogins([1, 10, 2,3, 4, 5, 6, 7, 8, 9]);
        array_unshift($logins, 'anonymous');
        return $logins;
    }

    private static function createManyUsers()
    {
        $model  = new Model();

        $model->addUser('login1', md5('pass'), 'email1@example.com', '2008-01-01 00:00:00');
        $model->addUser('login2', md5('pass'), 'email2@example.com', '2008-01-01 00:00:00');
        // login3 won't have access to any site
        $model->addUser('login3', md5('pass'), 'email3@example.com', '2008-01-01 00:00:00');
        $model->addUser('login4', md5('pass'), 'email4@example.com', '2008-01-01 00:00:00');
        $model->addUser('login5', md5('pass'), 'email5@example.com', '2008-01-01 00:00:00');
        $model->addUser('login6', md5('pass'), 'email6@example.com', '2008-01-01 00:00:00');
        $model->addUser('login7', md5('pass'), 'email7@example.com', '2008-01-01 00:00:00');
        $model->addUser('login8', md5('pass'), 'email8@example.com', '2008-01-01 00:00:00');
        $model->addUser('login9', md5('pass'), 'email9@example.com', '2008-01-01 00:00:00');
        $model->addUser('login10', md5('pass'), 'email10@example.com', '2008-01-01 00:00:00');
        $model->addUser('anonymous', '', 'ano@example.com', '2008-01-01 00:00:00');

        $model->setSuperUserAccess('login1', true); // we treat this one as our superuser

        foreach (self::$users as $login => $permissions) {
            foreach ($permissions as $access => $idSites) {
                $model->addUserAccess($login, $access, $idSites);
            }
        }
    }

    private function configureAccessForLogin($login)
    {
        $hasSuperUser = false;
        $idSitesAdmin = [];
        $idSitesWrite = [];
        $idSitesView  = [];

        if ($login === 'login1') {
            $hasSuperUser = true;
        } elseif (isset(self::$users[$login])) {
            $idSitesAdmin = self::$users[$login]['admin'];
            $idSitesWrite = self::$users[$login]['write'];
            $idSitesView  = self::$users[$login]['view'];
        }

        FakeAccess::clearAccess($hasSuperUser, $idSitesAdmin, $idSitesView, $login, $idSitesWrite);
    }
}
