<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Live;

use Exception;
use Piwik\DataTable;
use Piwik\Plugins\Live\Exception\MaxExecutionTimeExceededException;

class VisitorProfile
{
    const VISITOR_PROFILE_MAX_VISITS_TO_SHOW = 10;

    protected $profile = [];

    protected $idSite;

    public function __construct($idSite)
    {
        $this->idSite = $idSite;
    }

    /**
     * @param DataTable $visits
     * @param $visitorId
     * @param $segment
     * @param $numLastVisits
     * @return array
     * @throws Exception
     */
    public function makeVisitorProfile(DataTable $visits, $visitorId, $segment, $numLastVisits)
    {
        $visitorDetailsManipulators = Visitor::getAllVisitorDetailsInstances();

        $this->profile['visitorId']       = $visitorId;
        $this->profile['hasMoreVisits']   = $visits->getMetadata('hasMoreVisits');
        $this->profile['visit_first']     = $visits->getLastRow();
        $this->profile['visit_last']      = $visits->getFirstRow();

        foreach ($visitorDetailsManipulators as $instance) {
            $instance->initProfile($visits, $this->profile);
        }

        /** @var DataTable\Row $visit */
        foreach ($visits->getRows() as $visit) {
            foreach ($visitorDetailsManipulators as $instance) {
                $instance->handleProfileVisit($visit, $this->profile);
            }

            foreach ($visit->getColumn('actionDetails') as $action) {
                foreach ($visitorDetailsManipulators as $instance) {
                    $instance->handleProfileAction($action, $this->profile);
                }
            }
        }

        // use N most recent visits for last_visits
        $visits->deleteRowsOffset($numLastVisits);

        $this->profile['lastVisits'] = $visits;
        $this->handleAdjacentVisitorIds($visits, $visitorId, $segment);

        foreach ($visitorDetailsManipulators as $instance) {
            $instance->finalizeProfile($visits, $this->profile);
        }

        unset($this->profile['visit_first'], $this->profile['visit_last']);

        return $this->profile;
    }

    /**
     * @param DataTable $visits
     * @param           $visitorId
     * @param           $segment
     */
    private function handleAdjacentVisitorIds(DataTable $visits, $visitorId, $segment)
    {
        if (!$visits->getRowsCount()) {
            $this->profile['nextVisitorId'] = false;
            $this->profile['previousVisitorId'] = false;
            return;
        }
        // get visitor IDs that are adjacent to this one in log_visit
        // TODO: make sure order of visitor ids is not changed if a returning visitor visits while the user is
        //       looking at the popup.
        $rows            = $visits->getRows();
        $latestVisitTime = reset($rows)->getColumn('lastActionDateTime');

        $model = new Model();
        try {
            $this->profile['nextVisitorId'] = $model->queryAdjacentVisitorId($this->idSite, $visitorId, $latestVisitTime, $segment, $getNext = true);
        } catch (MaxExecutionTimeExceededException $e) {
            $this->profile['nextVisitorId'] = false;
            $this->profile['previousVisitorId'] = false; // if query for next visitor is too slow, we assume query for previous visitor is too slow too
            return;
        }
        try {
            $this->profile['previousVisitorId'] = $model->queryAdjacentVisitorId($this->idSite, $visitorId, $latestVisitTime, $segment, $getNext = false);
        } catch (MaxExecutionTimeExceededException $e) {
            // we simply assume there is no previous visitor in that case
            $this->profile['previousVisitorId'] = false;
        }
    }
}
