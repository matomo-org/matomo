<?php

namespace Piwik\Plugins\ExampleLogTables\Columns;

use Piwik\Columns\Dimension;

class GroupAttributeAdmin extends Dimension
{
    protected $dbTableName  = 'log_group';
    protected $category     = 'General_Visitors';
    protected $type         = self::TYPE_BOOL;
    protected $columnName   = 'is_admin';
    protected $segmentName  = 'isadmin';
    protected $nameSingular = 'Admin privileges';
}
