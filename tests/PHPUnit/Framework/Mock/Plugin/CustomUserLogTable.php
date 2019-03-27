<?php

namespace Piwik\Tests\Framework\Mock\Plugin;

use Piwik\Tracker\LogTable;

class CustomUserLogTable extends LogTable
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
