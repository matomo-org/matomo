<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Columns;

use Piwik\Plugin\Dimension\ConversionDimension;

class IdGoal extends ConversionDimension
{
    protected $columnName = 'idgoal';
    protected $type = self::TYPE_TEXT;
    protected $category = 'General_Visit';
    protected $nameSingular = 'General_VisitConvertedGoalId';
    protected $segmentName = 'visitConvertedGoalId';
    protected $acceptValues = '1, 2, 3, etc.';

}