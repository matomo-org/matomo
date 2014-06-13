<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Plugin\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker;

class VisitFirstActionTime extends VisitDimension
{
    protected $fieldName = 'visit_first_action_time';
    protected $fieldType = 'DATETIME NOT NULL';

    public function getName()
    {
        return '';
    }

    /**
     * @param Request $request
     * @param array   $visit
     * @param Action|null $action
     * @return int
     */
    public function onNewVisit(Request $request, $visit, $action)
    {
        return Tracker::getDatetimeFromTimestamp($request->getCurrentTimestamp());
    }
}