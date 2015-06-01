<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests;

use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API;
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
}
