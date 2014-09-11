<?php
/**
 * Piwik - free/libre analytics platform
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

    /**
     * @see getHighestCustomVarIndex()
     * @return int
     */
    public function getCurrentNumCustomVars()
    {
        $indexes = $this->getCustomVarIndexes();

        return count($indexes);
    }

    /**
     * result of getHighestCustomVarIndex() can be different to getCurrentNumCustomVars() in case there are some missing
     * custom variable indexes. For instance in case of manual changes on the DB
     *
     * custom_var_v1
     * custom_var_v2
     * custom_var_v4
     *
     * getHighestCustomVarIndex() -> returns 4
     * getCurrentNumCustomVars() -> returns 3
     *
     * @return int
     */
    public function getHighestCustomVarIndex()
    {
        $indexes = $this->getCustomVarIndexes();

        if (empty($indexes)) {
            return 0;
        }

        return max($indexes);
    }

    public function getCustomVarIndexes()
    {
        $columns = $this->getCustomVarColumnNames();

        if (empty($columns)) {
            return array();
        }

        $indexes = array_map(function ($column) {
            return Model::getCustomVariableIndexFromFieldName($column);
        }, $columns);

        return array_values(array_unique($indexes));
    }

    private function getCustomVarColumnNames()
    {
        $dbTable = $this->getDbTableName();
        $columns = Db::getColumnNamesFromTable($dbTable);

        $customVarColumns = array_filter($columns, function ($column) {
            return false !== strpos($column, 'custom_var_');
        });

        return $customVarColumns;
    }

    public function removeCustomVariable()
    {
        $dbTable = $this->getDbTableName();
        $index   = $this->getHighestCustomVarIndex();

        if ($index < 1) {
            return null;
        }

        Db::exec(sprintf('ALTER TABLE %s ', $dbTable)
               . sprintf('DROP COLUMN custom_var_k%d,', $index)
               . sprintf('DROP COLUMN custom_var_v%d;', $index));

        return $index;
    }

    public function addCustomVariable()
    {
        $dbTable = $this->getDbTableName();
        $index   = $this->getHighestCustomVarIndex() + 1;
        $maxLen  = CustomVariables::getMaxLengthCustomVariables();

        Db::exec(sprintf('ALTER TABLE %s ', $dbTable)
               . sprintf('ADD COLUMN custom_var_k%d VARCHAR(%d) DEFAULT NULL,', $index, $maxLen)
               . sprintf('ADD COLUMN custom_var_v%d VARCHAR(%d) DEFAULT NULL;', $index, $maxLen));

        return $index;
    }

    private function getDbTableName()
    {
        return Common::prefixTable($this->scope);
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

    public static function install()
    {
        foreach (self::getScopes() as $scope) {
            $model = new Model($scope);

            try {
                $maxCustomVars   = 5;
                $customVarsToAdd = $maxCustomVars - $model->getCurrentNumCustomVars();

                for ($index = 0; $index < $customVarsToAdd; $index++) {
                    $model->addCustomVariable();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to add custom variable: ' . $e->getMessage());
            }
        }
    }

    public static function uninstall()
    {
        foreach (self::getScopes() as $scope) {
            $model = new Model($scope);

            while ($model->getHighestCustomVarIndex()) {
                $model->removeCustomVariable();
            }
        }
    }

}

