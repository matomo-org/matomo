<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Columns\Dimension;
use Piwik\Piwik;

class CustomDimension extends Dimension
{
    protected $type = self::TYPE_TEXT;

    private $id;

    public function getId()
    {
        return $this->id;
    }

    public function initCustomDimension($index, Model $scope)
    {
        $category = $scope->getScopeDescription();

        $this->id = 'CustomVariables.CustomVariable' . ucfirst($scope->getScope()) . $index;
        $this->nameSingular = Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $index . ' (' . $category .')';
        $this->columnName = 'custom_var_v' . (int) $index;
        $this->category = 'CustomVariables_CustomVariables';
        $this->dbTableName = $scope->getUnprefixedTableName();
    }

}