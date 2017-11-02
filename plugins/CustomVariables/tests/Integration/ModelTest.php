<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\tests;
use Piwik\Db;
use Piwik\Plugins\CustomVariables\Model;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomVariables
 * @group ModelTest
 * @group Plugins
 * @group CustomVariables_ModelTest
 */
class ModelTest extends IntegrationTestCase
{
    private static $cvarScopes = array('page', 'visit', 'conversion');

    public function setUp()
    {
        // do not call parent::setUp() since it expects database to be created,
        // but DB for this test is removed in tearDown

        self::$fixture->performSetUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        self::$fixture->performTearDown();
    }

    /**
     * @expectedException \Exception
     */
    public function test_construct_shouldFailInCaseOfEmptyScope()
    {
        new Model(null);
    }

    /**
     * @expectedException \Exception
     */
    public function test_construct_shouldFailInCaseOfInvalidScope()
    {
        new Model('inValId');
    }

    public function testGetAllScopes()
    {
        $this->assertEquals(self::$cvarScopes, Model::getScopes());
    }

    public function test_Install_Uninstall()
    {
        $this->assertEquals(5, $this->getPageScope()->getCurrentNumCustomVars());
        $this->assertEquals(5, $this->getVisitScope()->getCurrentNumCustomVars());
        $this->assertEquals(5, $this->getConversionScope()->getCurrentNumCustomVars());

        Model::uninstall();

        $this->assertEquals(0, $this->getPageScope()->getCurrentNumCustomVars());
        $this->assertEquals(0, $this->getVisitScope()->getCurrentNumCustomVars());
        $this->assertEquals(0, $this->getConversionScope()->getCurrentNumCustomVars());

        $this->getPageScope()->addCustomVariable();
        $this->getPageScope()->addCustomVariable();
        $this->getVisitScope()->addCustomVariable();

        $this->assertEquals(2, $this->getPageScope()->getCurrentNumCustomVars());
        $this->assertEquals(1, $this->getVisitScope()->getCurrentNumCustomVars());
        $this->assertEquals(0, $this->getConversionScope()->getCurrentNumCustomVars());

        Model::install();

        $this->assertEquals(5, $this->getPageScope()->getCurrentNumCustomVars());
        $this->assertEquals(5, $this->getVisitScope()->getCurrentNumCustomVars());
        $this->assertEquals(5, $this->getConversionScope()->getCurrentNumCustomVars());
    }

    public function testGetCustomVariableIndexFromFieldName()
    {
        $this->assertSame(0, Model::getCustomVariableIndexFromFieldName('custom_var_k0'));
        $this->assertSame(0, Model::getCustomVariableIndexFromFieldName('custom_var_v0'));
        $this->assertSame(5, Model::getCustomVariableIndexFromFieldName('custom_var_k5'));
        $this->assertSame(5, Model::getCustomVariableIndexFromFieldName('custom_var_v5'));
        $this->assertSame(938, Model::getCustomVariableIndexFromFieldName('custom_var_k938'));
        $this->assertSame(938, Model::getCustomVariableIndexFromFieldName('custom_var_v938'));
        $this->assertSame(null, Model::getCustomVariableIndexFromFieldName('otherfield'));
    }

    public function testGetScopeName()
    {
        $this->assertEquals('Page', $this->getPageScope()->getScopeName());
        $this->assertEquals('Visit', $this->getVisitScope()->getScopeName());
        $this->assertEquals('Conversion', $this->getConversionScope()->getScopeName());
    }

    public function testGetScopeDescription()
    {
        $this->assertEquals('scope page', $this->getPageScope()->getScopeDescription());
        $this->assertEquals('scope visit', $this->getVisitScope()->getScopeDescription());
        $this->assertEquals('scope conversion', $this->getConversionScope()->getScopeDescription());
    }

    public function testGetUnprefixedTableName()
    {
        $this->assertEquals('log_link_visit_action', $this->getPageScope()->getUnprefixedTableName());
        $this->assertEquals('log_visit', $this->getVisitScope()->getUnprefixedTableName());
        $this->assertEquals('log_conversion', $this->getConversionScope()->getUnprefixedTableName());
    }

    public function testGetScope()
    {
        $this->assertEquals(Model::SCOPE_PAGE, $this->getPageScope()->getScope());
        $this->assertEquals(Model::SCOPE_VISIT, $this->getVisitScope()->getScope());
    }

    public function test_getCurrentNumCustomVars()
    {
        $this->assertEquals(5, $this->getPageScope()->getCurrentNumCustomVars());
        $this->assertEquals(5, $this->getVisitScope()->getCurrentNumCustomVars());
        $this->assertEquals(5, $this->getConversionScope()->getCurrentNumCustomVars());

        $this->getPageScope()->addCustomVariable();
        $this->getConversionScope()->removeCustomVariable();

        $this->assertEquals(6, $this->getPageScope()->getCurrentNumCustomVars());
        $this->assertEquals(5, $this->getVisitScope()->getCurrentNumCustomVars());
        $this->assertEquals(4, $this->getConversionScope()->getCurrentNumCustomVars());
    }

    public function test_getCustomVarIndexes()
    {
        $this->assertEquals(array(1,2,3,4,5), $this->getPageScope()->getCustomVarIndexes());
        $this->assertEquals(array(1,2,3,4,5), $this->getVisitScope()->getCustomVarIndexes());
        $this->assertEquals(array(1,2,3,4,5), $this->getConversionScope()->getCustomVarIndexes());

        $this->getPageScope()->addCustomVariable();
        $this->getConversionScope()->removeCustomVariable();

        $this->assertEquals(array(1,2,3,4,5,6), $this->getPageScope()->getCustomVarIndexes());
        $this->assertEquals(array(1,2,3,4,5), $this->getVisitScope()->getCustomVarIndexes());
        $this->assertEquals(array(1,2,3,4), $this->getConversionScope()->getCustomVarIndexes());
    }

    public function test_getHighestCustomVarIndex_addCustomVariable_removeCustomVariable()
    {
        $this->assertEquals(5, $this->getPageScope()->getHighestCustomVarIndex());
        $this->assertEquals(5, $this->getVisitScope()->getHighestCustomVarIndex());
        $this->assertEquals(5, $this->getConversionScope()->getHighestCustomVarIndex());

        $this->getPageScope()->addCustomVariable();
        $this->getConversionScope()->removeCustomVariable();

        $this->assertEquals(6, $this->getPageScope()->getHighestCustomVarIndex());
        $this->assertEquals(5, $this->getVisitScope()->getHighestCustomVarIndex());
        $this->assertEquals(4, $this->getConversionScope()->getHighestCustomVarIndex());

        $this->getConversionScope()->removeCustomVariable();
        $this->getPageScope()->addCustomVariable();
        $this->getVisitScope()->addCustomVariable();
        $this->getPageScope()->addCustomVariable();
        $this->getConversionScope()->removeCustomVariable();

        $this->assertEquals(8, $this->getPageScope()->getHighestCustomVarIndex());
        $this->assertEquals(6, $this->getVisitScope()->getHighestCustomVarIndex());
        $this->assertEquals(2, $this->getConversionScope()->getHighestCustomVarIndex());
    }

    public function test_removeCustomVariable_shouldNotFailIfRemovesMoreThanExist()
    {
        $scope = $this->getPageScope();

        $this->assertEquals(5, $scope->getHighestCustomVarIndex());

        for ($index = 0; $index < 5; $index++) {
            $scope->removeCustomVariable();
            $this->assertEquals(4 - $index, $scope->getHighestCustomVarIndex());
        }

        $this->assertNull($scope->removeCustomVariable());
        $this->assertEquals(0, $scope->getHighestCustomVarIndex());
        $this->assertEquals(0, $scope->getCurrentNumCustomVars());
    }

    public function test_removeCustomVariable_addCustomVariable_ReturnsIndex()
    {
        $scopeToAdd = $this->getPageScope();
        $scopeToRemove = $this->getVisitScope();

        for ($index = 0; $index < 5; $index++) {
            $this->assertEquals(5 - $index, $scopeToRemove->removeCustomVariable());
            $this->assertEquals(6 + $index, $scopeToAdd->addCustomVariable());
        }
    }

    private function getPageScope()
    {
        return new Model(Model::SCOPE_PAGE);
    }

    private function getVisitScope()
    {
        return new Model(Model::SCOPE_VISIT);
    }

    private function getConversionScope()
    {
        return new Model(Model::SCOPE_CONVERSION);
    }

}
