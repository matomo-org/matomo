<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\DataTable\Filter;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\DataTable;

class Actions extends BaseFilter
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
        $table->filter(function (DataTable $dataTable) {

            $defaultActionName = Config::getInstance()->General['action_default_name'];

            foreach ($dataTable->getRows() as $row) {
                $url = $row->getMetadata('url');
                if ($url) {
                    $row->setMetadata('segmentValue', urldecode($url));
                }

                // remove the default action name 'index' in the end of flattened urls
                if (Common::getRequestVar('flat', 0)) {
                    $label = $row->getColumn('label');
                    if (substr($label, -strlen($defaultActionName)) == $defaultActionName) {
                        $label = substr($label, 0, -strlen($defaultActionName));
                        if (substr($label, -1) == '/') {
                            $label = rtrim($label, '/') . '/';
                        }
                        $row->setColumn('label', $label);
                    }
                    $dataTable->setLabelsHaveChanged();
                }
            }
        });

        // TODO can we remove this one again?
        $table->queueFilter('GroupBy', array('label', function ($label) {
            return urldecode($label);
        }));

        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->filter($subtable);
            }
        }
    }
}