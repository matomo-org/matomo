<?php

namespace Piwik\Tests\Framework\Mock\Plugin;

use Piwik\Tracker\LogTable;

class OtherCustomUserLogTable extends LogTable
{
    public function getName()
    {
        return 'log_custom_other';
    }

    public function getIdColumn()
    {
        return 'other_id';
    }

    public function getPrimaryKey()
    {
        return ['other_id'];
    }

    public function getWaysToJoinToOtherLogTables()
    {
        return ['log_custom' => 'other_id'];
    }
}
