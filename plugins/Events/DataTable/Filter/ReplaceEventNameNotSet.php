<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\Events\Archiver;

class ReplaceEventNameNotSet extends BaseFilter
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
        $row = $table->getRowFromLabel(Archiver::EVENT_NAME_NOT_SET);
        if ($row) {
            $row->setColumn('label', Piwik::translate('General_NotDefined', Piwik::translate('Events_EventName')));
        }
    }
}