<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Log;

class Model
{
    const SCOPE_PAGE = 'log_link_visit_action';
    const SCOPE_VISIT = 'log_visit';
    const SCOPE_CONVERSION = 'log_conversion';

    private $scope = null;

    public function __construct($scope)
    {
        if (empty($scope) || !in_array($scope, $this->getScopes())) {
            throw new \Exception('Invalid custom variable scope');
        }

        $this->scope = $scope;
    }

    public function getScopeName()
    {
        // actually we should have a class for each scope but don't want to overengineer it for now
        switch ($this->scope) {
            case self::SCOPE_PAGE:
                return 'Page';
            case self::SCOPE_VISIT:
                return 'Visit';
            case self::SCOPE_CONVERSION:
                return 'Conversion';
        }
    }

    public function getCurrentNumCustomVars()
    {
        $customVarColumns = $this->getCustomVarColumnNames();

        $currentNumCustomVars = count($customVarColumns) / 2;

        return (int) $currentNumCustomVars;
    }

    public function getHighestCustomVarIndex()
    {
        $columns = $this->getCustomVarColumnNames();

        if (empty($columns)) {
            return 0;
        }

        $indexes = array_map(function ($column) {
            return Model::getCustomVariableIndexFromFieldName($column);
        }, $columns);

        return max($indexes);
    }

    private function getCustomVarColumnNames()
    {
        $dbTable = Common::prefixTable($this->scope);
        $columns = Db::getColumnNamesFromTable($dbTable);

        $customVarColumns = array_filter($columns, function ($column) {
            return false !== strpos($column, 'custom_var_');
        });

        return $customVarColumns;
    }

    public function removeCustomVariable()
    {
        $dbTable = Common::prefixTable($this->scope);
        $index   = $this->getHighestCustomVarIndex();

        if ($index < 1) {
            return null;
        }

        Db::exec(sprintf('ALTER TABLE %s DROP COLUMN custom_var_k%d', $dbTable, $index));
        Db::exec(sprintf('ALTER TABLE %s DROP COLUMN custom_var_v%d', $dbTable, $index));

        return $index;
    }

    public function addCustomVariable()
    {
        $dbTable = Common::prefixTable($this->scope);
        $index   = $this->getHighestCustomVarIndex() + 1;

        Db::exec(sprintf('ALTER TABLE %s ADD COLUMN custom_var_k%d VARCHAR(%d) DEFAULT NULL', $dbTable, $index, self::getMaxLengthCustomVariables()));
        Db::exec(sprintf('ALTER TABLE %s ADD COLUMN custom_var_v%d VARCHAR(%d) DEFAULT NULL', $dbTable, $index, self::getMaxLengthCustomVariables()));

        return $index;
    }

    public static function getCustomVariableIndexFromFieldName($fieldName)
    {
        $onlyNumber = str_replace(array('custom_var_k', 'custom_var_v'), '', $fieldName);

        if (is_numeric($onlyNumber)) {
            return (int) $onlyNumber;
        }
    }

    public static function getScopes()
    {
        return array(self::SCOPE_PAGE, self::SCOPE_VISIT, self::SCOPE_CONVERSION);
    }

    public static function getMaxCustomVariables()
    {
        return 5;
    }

    /**
     * There are also some hardcoded places in JavaScript
     * @return int
     */
    public static function getMaxLengthCustomVariables()
    {
        return 200;
    }

    public static function install()
    {
        foreach (self::getScopes() as $scope) {
            $model = new Model($scope);

            try {
                for ($index = 0; $index < 5; $index++) {
                    $model->addCustomVariable();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to add custom variable: ' . $e->getMessage());
            }
        }
    }

}

