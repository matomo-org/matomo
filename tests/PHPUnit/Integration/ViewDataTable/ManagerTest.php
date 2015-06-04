<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ViewDataTable;

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
