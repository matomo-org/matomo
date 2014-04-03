<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\tests;
use Piwik\Db;
use Piwik\Plugins\CustomVariables\Model;

/**
 * @group CustomVariables
 * @group ModelTest
 * @group Database
 */
class ModelTest extends \DatabaseTestCase
{
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
        $this->assertEquals(array('log_link_visit_action', 'log_visit', 'log_conversion'), Model::getScopes());
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
