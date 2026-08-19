<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Columns;

use Piwik\Columns\Dimension;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;

class UserAttributeGender extends Dimension
{
    protected $dbTableName  = CustomUserLog::TABLE_NAME;
    protected $category     = 'General_Visitors';
    protected $type         = self::TYPE_TEXT;
    protected $columnName   = 'gender';
    protected $segmentName  = 'attrgender';
    protected $nameSingular = 'ExampleLogTables_UserGender';
}
