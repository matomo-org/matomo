<?php

namespace Piwik\Plugins\ExampleLogTables\Columns;

use Piwik\Columns\Dimension;

class UserAttributeGender extends Dimension
{
    protected $dbTableName  = 'log_custom';
    protected $category     = 'General_Visitors';
    protected $type         = self::TYPE_TEXT;
    protected $columnName   = 'gender';
    protected $segmentName  = 'attrgender';
    protected $nameSingular = 'Gender';
}
