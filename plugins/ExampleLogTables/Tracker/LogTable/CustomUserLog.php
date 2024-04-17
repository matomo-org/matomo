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

class CustomUserLog extends LogTable
{
    public function getName()
    {
        return 'log_custom';
    }

    public function getIdColumn()
    {
        return 'user_id';
    }

    public function getPrimaryKey()
    {
        return ['user_id'];
    }

    public function getWaysToJoinToOtherLogTables()
    {
        return ['log_visit' => 'user_id'];
    }
}
