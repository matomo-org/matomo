<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class TimeSpentRefAction extends ActionDimension
{
    protected $columnName = 'time_spent_ref_action';
    protected $columnType = 'INTEGER(10) UNSIGNED NULL';
    protected $type = self::TYPE_DURATION_S;
    protected $nameSingular = 'Actions_TimeSpentInReferringAction';

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        $timeSpent = $visitor->getVisitorColumn('time_spent_ref_action');

        if (empty($timeSpent)) {
            return 0;
        }

        return $timeSpent;
    }
}
