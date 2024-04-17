<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ViewDataTable;

use Piwik\Option;
use Piwik\ViewDataTable\Manager as ViewDataTableManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group ViewDataTable
 */
class ManagerTest extends IntegrationTestCase
{
    public function test_getViewDataTableParameters_shouldReturnEmptyArray_IfNothingPersisted()
    {
        $login        = 'mylogin';
        $method       = 'API.get';
        $storedParams = ViewDataTableManager::getViewDataTableParameters($login, $method);

        $this->assertEquals(array(), $storedParams);
    }

    public function test_getViewDataTableParameters_shouldOnlyReturnParams_IfLoginAndActionMatches()
    {
        $params = $this->addParameters();

        $storedParams = ViewDataTableManager::getViewDataTableParameters('WroNgLogIn', $params['method']);
        $this->assertEquals(array(), $storedParams);

        $storedParams = ViewDataTableManager::getViewDataTableParameters($params['login'], 'API.wRoNg');
        $this->assertEquals(array(), $storedParams);

        $storedParams = ViewDataTableManager::getViewDataTableParameters($params['login'], $params['method']);
        $this->assertEquals($params['params'], $storedParams);
    }

    public function test_setViewDataTableParameters_inConfigProperty_shouldOnlyAllowOverridableParams()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Setting parameters translations is not allowed. Please report this bug to the Matomo team.');

        $login  = 'mylogin';
        $method = 'API.get';
        $params = array(
            'flat' => '0',
            'translations' => 'this is not overridable param and should fail',
            'viewDataTable' => 'tableAllColumns'
        );

        ViewDataTableManager::saveViewDataTableParameters($login, $method, $params);
    }

    public function test_setViewDataTableParameters_inConfigProperty_shouldOnlyAllowOverridableParams_bis()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Setting parameters filters is not allowed. Please report this bug to the Matomo team.');

        $login  = 'mylogin';
        $method = 'API.get';
        $params = array(
            'flat' => '0',
            'filters' => 'this is not overridable param and should fail',
            'viewDataTable' => 'tableAllColumns'
        );

        ViewDataTableManager::saveViewDataTableParameters($login, $method, $params);
    }

    public function test_setViewDataTableParameters_inRequestConfigProperty_shouldOnlyAllowOverridableParams()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Setting parameters apiMethodToRequestDataTable is not allowed. Please report this bug to the Matomo team.');

        $login  = 'mylogin';
        $method = 'API.get';
        $params = array(
            'flat' => '0',
            'apiMethodToRequestDataTable' => 'this is not overridable in RequestConfig param and should fail',
            'viewDataTable' => 'tableAllColumns'
        );

        ViewDataTableManager::saveViewDataTableParameters($login, $method, $params);
    }

    public function test_getViewDataTableParameters_removesNonOverridableParameter()
    {
        $params = array(
            'flat' => '0',
            'filters' => 'this is not overridable param and should fail',
            'viewDataTable' => 'tableAllColumns'
        );

        // 'filters' was removed
        $paramsExpectedWhenFetched = array(
            'flat' => '0',
            'viewDataTable' => 'tableAllColumns'
        );

        $login  = 'mylogin';
        $controllerAction = 'API.get';

        // simulate an invalid list of parameters (contains 'filters')
        $paramsKey = sprintf('viewDataTableParameters_%s_%s', $login, $controllerAction);
        Option::set($paramsKey, json_encode($params));

        // check the invalid list is fetched without the overridable parameter
        $processed = ViewDataTableManager::getViewDataTableParameters($login, $controllerAction);
        $this->assertEquals($paramsExpectedWhenFetched, $processed);
    }

    public function test_clearAllViewDataTableParameters_shouldRemoveAllPersistedParameters()
    {
        ViewDataTableManager::saveViewDataTableParameters('mylogin1', 'API.get1', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin1', 'API.get2', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin2', 'API.get3', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin1', 'API.get4', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin3', 'API.get5', array('flat' => 1));

        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin3', 'API.get5'));

        ViewDataTableManager::clearAllViewDataTableParameters();

        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin3', 'API.get5'));
    }

    public function test_clearUserViewDataTableParameters_shouldOnlyRemoveAUsersParameters()
    {
        ViewDataTableManager::saveViewDataTableParameters('mylogin1', 'API.get1', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin1', 'API.get2', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin2', 'API.get3', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin1', 'API.get4', array('flat' => 1));
        ViewDataTableManager::saveViewDataTableParameters('mylogin3', 'API.get5', array('flat' => 1));

        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin3', 'API.get5'));

        ViewDataTableManager::clearUserViewDataTableParameters('mylogin1');

        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertNotEmpty(ViewDataTableManager::getViewDataTableParameters('mylogin3', 'API.get5'));
    }

    private function addParameters()
    {
        $login  = 'mylogin';
        $method = 'API.get';
        $params = array('flat' => '0', 'expanded' => 1, 'viewDataTable' => 'tableAllColumns');

        ViewDataTableManager::saveViewDataTableParameters($login, $method, $params);

        return array('login' => $login, 'method' => $method, 'params' => $params);
    }
}
