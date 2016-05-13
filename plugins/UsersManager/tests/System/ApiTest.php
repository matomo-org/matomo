<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\System;

use Piwik\Plugins\UsersManager\tests\Fixtures\ManyUsers;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group UsersManager
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var ManyUsers
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params = array())
    {
        $apiId = implode('_', $params);
        $logins = array(
            'login1' => 'when_superuseraccess',
            'login2' => 'when_adminaccess',
            'login4' => 'when_viewaccess'
        );

        // login1 = super user, login2 = some admin access, login4 = only view access
        foreach ($logins as $login => $appendix) {
            $params['token_auth'] = self::$fixture->users[$login]['token'];

            $this->runAnyApiTest($api, $apiId . '_' . $appendix, $params, array('xmlFieldsToRemove' => array('date_registered')));
        }
    }

    public function getApiForTesting()
    {
        $apiToTest = array(
            array('UsersManager.getUsers'),
            array('UsersManager.getUsersLogin'),
            array('UsersManager.getUsersAccessFromSite', array('idSite' => 6)), // admin user has admin access for this
            array('UsersManager.getUsersAccessFromSite', array('idSite' => 3)), // admin user has only view access for this, should not see anything
            array('UsersManager.getUsersSitesFromAccess', array('access' => 'admin')),
            array('UsersManager.getUsersWithSiteAccess', array('idSite' => 3, 'access' => 'admin')),
            array('UsersManager.getUser', array('userLogin' => 'login1')),
            array('UsersManager.getUser', array('userLogin' => 'login2')),
            array('UsersManager.getUser', array('userLogin' => 'login4')),
            array('UsersManager.getUser', array('userLogin' => 'login6')),
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

ApiTest::$fixture = new ManyUsers();