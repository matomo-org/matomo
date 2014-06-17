<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class ReferrerVisitServerDate extends Base
{
    protected $fieldName = 'referer_visit_server_date';
    protected $fieldType = 'date default NULL';

    public function getName()
    {
        return '';
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onRecordGoal(Request $request, Visitor $visitor, $action)
    {
        return $this->getValueForRecordGoal($request, $visitor);
    }
}
