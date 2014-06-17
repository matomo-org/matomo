<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Plugin\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker;

class TimeSpentRefAction extends ActionDimension
{
    protected $fieldName = 'time_spent_ref_action';
    protected $fieldType = 'INTEGER(10) UNSIGNED NOT NULL';

    public function getName()
    {
        return '';
    }

    public function onNewAction(Request $request, Action $action, Tracker\Visitor $visitor)
    {
        $timeSpent = $visitor->getVisitorColumn('time_spent_ref_action');

        if (empty($timeSpent)) {
            return 0;
        }

        return $timeSpent;
    }
}
