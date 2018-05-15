<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Piwik;

class CustomVariablesValuesFromNameId extends BaseFilter
{

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $notDefinedLabel = Piwik::translate('General_NotDefined', Piwik::translate('CustomVariables_ColumnCustomVariableValue'));

        $table->queueFilter('ColumnCallbackReplace', array('label', function ($label) use ($notDefinedLabel) {
            return $label == \Piwik\Plugins\CustomVariables\Archiver::LABEL_CUSTOM_VALUE_NOT_DEFINED
                ? $notDefinedLabel
                : $label;
        }));
    }
}