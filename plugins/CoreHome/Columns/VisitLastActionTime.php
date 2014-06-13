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

class VisitLastActionTime extends VisitDimension
{
    protected $fieldName = 'visit_last_action_time';
    // we do not install or define column definition here as we need to create this column when installing as there is
    // an index on it. Currently we do not define the index here... although we could overwrite the install() method
    // and add column 'visit_last_action_time' and add index. Problem is there is also an index
    // INDEX(idsite, config_id, visit_last_action_time) and we maybe not be sure whether config_id already exists at
    // installing point (in case config_id is installed via dimension as well we do not know which column will be added
    // first).

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

    /**
     * @param Request $request
     * @param array   $visit
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, $visit, $action)
    {
        return $this->onNewVisit($request, $visit, $action);
    }

}