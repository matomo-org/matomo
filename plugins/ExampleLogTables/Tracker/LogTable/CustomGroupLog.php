<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExampleLogTables\Tracker\LogTable;

use Piwik\Tracker\LogTable;

class CustomGroupLog extends LogTable
{
    public function getName()
    {
        return 'log_group';
    }

    public function getIdColumn()
    {
        return 'group';
    }

    public function getPrimaryKey()
    {
        return ['group'];
    }

    public function getWaysToJoinToOtherLogTables()
    {
        return ['log_custom' => 'group'];
    }
}
