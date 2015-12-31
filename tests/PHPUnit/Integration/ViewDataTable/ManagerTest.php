<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ViewDataTable;

use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\ViewDataTable\Manager as ViewDataTableManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group ViewDataTable
 */
class ManagerTest extends IntegrationTestCase
{
    /**
     * @var ViewDataTableManager
     */
    private $viewDataTableManager;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->viewDataTableManager = StaticContainer::get('Piwik\ViewDataTable\Manager');
    }
    
    public function test_getViewDataTableParameters_shouldReturnEmptyArray_IfNothingPersisted()
    {
        $login        = 'mylogin';
        $method       = 'API.get';
        $storedParams = $this->viewDataTableManager->getViewDataTableParameters($login, $method);

        $this->assertEquals(array(), $storedParams);
    }

    public function test_getViewDataTableParameters_shouldOnlyReturnParams_IfLoginAndActionMatches()
    {
        $params = $this->addParameters();

        $storedParams = $this->viewDataTableManager->getViewDataTableParameters('WroNgLogIn', $params['method']);
        $this->assertEquals(array(), $storedParams);

        $storedParams = $this->viewDataTableManager->getViewDataTableParameters($params['login'], 'API.wRoNg');
        $this->assertEquals(array(), $storedParams);

        $storedParams = $this->viewDataTableManager->getViewDataTableParameters($params['login'], $params['method']);
        $this->assertEquals($params['params'], $storedParams);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Setting parameters translations is not allowed. Please report this bug to the Piwik team.
     */
    public function test_setViewDataTableParameters_inConfigProperty_shouldOnlyAllowOverridableParams()
    {
        $login  = 'mylogin';
        $method = 'API.get';
        $params = array(
            'flat' => '0',
            'translations' => 'this is not overridable param and should fail',
            'viewDataTable' => 'tableAllColumns'
        );

        $this->viewDataTableManager->saveViewDataTableParameters($login, $method, $params);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Setting parameters filters is not allowed. Please report this bug to the Piwik team.
     */
    public function test_setViewDataTableParameters_inConfigProperty_shouldOnlyAllowOverridableParams_bis()
    {
        $login  = 'mylogin';
        $method = 'API.get';
        $params = array(
            'flat' => '0',
            'filters' => 'this is not overridable param and should fail',
            'viewDataTable' => 'tableAllColumns'
        );

        $this->viewDataTableManager->saveViewDataTableParameters($login, $method, $params);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Setting parameters apiMethodToRequestDataTable is not allowed. Please report this bug to the Piwik team.
     */
    public function test_setViewDataTableParameters_inRequestConfigProperty_shouldOnlyAllowOverridableParams()
    {
        $login  = 'mylogin';
        $method = 'API.get';
        $params = array(
            'flat' => '0',
            'apiMethodToRequestDataTable' => 'this is not overridable in RequestConfig param and should fail',
            'viewDataTable' => 'tableAllColumns'
        );

        $this->viewDataTableManager->saveViewDataTableParameters($login, $method, $params);
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
        $processed = $this->viewDataTableManager->getViewDataTableParameters($login, $controllerAction);
        $this->assertEquals($paramsExpectedWhenFetched, $processed);

    }

    public function test_clearAllViewDataTableParameters_shouldRemoveAllPersistedParameters()
    {
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin1', 'API.get1', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin1', 'API.get2', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin2', 'API.get3', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin1', 'API.get4', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin3', 'API.get5', array('flat' => 1));

        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin3', 'API.get5'));

        $this->viewDataTableManager->clearAllViewDataTableParameters();

        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin3', 'API.get5'));
    }

    public function test_clearUserViewDataTableParameters_shouldOnlyRemoveAUsersParameters()
    {
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin1', 'API.get1', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin1', 'API.get2', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin2', 'API.get3', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin1', 'API.get4', array('flat' => 1));
        $this->viewDataTableManager->saveViewDataTableParameters('mylogin3', 'API.get5', array('flat' => 1));

        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin3', 'API.get5'));

        $this->viewDataTableManager->clearUserViewDataTableParameters('mylogin1');

        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get1'));
        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get2'));
        $this->assertEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin1', 'API.get4'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin2', 'API.get3'));
        $this->assertNotEmpty($this->viewDataTableManager->getViewDataTableParameters('mylogin3', 'API.get5'));
    }

    private function addParameters()
    {
        $login  = 'mylogin';
        $method = 'API.get';
        $params = array('flat' => '0', 'expanded' => 1, 'viewDataTable' => 'tableAllColumns');

        $this->viewDataTableManager->saveViewDataTableParameters($login, $method, $params);

        return array('login' => $login, 'method' => $method, 'params' => $params);
    }
}
