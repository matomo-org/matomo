<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Tracker\LogTable;

use Piwik\Tracker\LogTable;

class ConversionItem extends LogTable
{
    public function getName()
    {
        return 'log_conversion_item';
    }

    public function getIdColumn()
    {
        return 'idvisit';
    }

    public function getColumnToJoinOnIdVisit()
    {
        return 'idvisit';
    }

    public function getPrimaryKey()
    {
        return array('idvisit', 'idorder', 'idaction_sku');
    }

}
