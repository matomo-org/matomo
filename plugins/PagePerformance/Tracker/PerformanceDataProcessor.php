<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\PagePerformance\Tracker;

use Piwik\Common;
use Piwik\Log;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\PagePerformance\Columns\TimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\TimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\TimeNetwork;
use Piwik\Plugins\PagePerformance\Columns\TimeServer;
use Piwik\Plugins\PagePerformance\Columns\TimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\TimeTransfer;
use Piwik\Tracker;
use Piwik\Tracker\Action;
use Piwik\Tracker\ActionPageview;
use Piwik\Tracker\Model;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Handles tracker requests containing performance data.
 */
class PerformanceDataProcessor extends RequestProcessor
{
    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        /** @var null|Action $action */
        $action = $request->getMetadata('Actions', 'action');

        // update performance metrics only for non pageview requests
        if ($action && $action instanceof ActionPageview) {
            return;
        }

        $pageViewId = $request->getParam('pv_id');
        $idVisit    = $visitProperties->getProperty('idvisit');

        // ignore requests that can't be associated with an existing page view of a visitor
        if (empty($pageViewId) || empty($idVisit)) {
            return;
        }

        /** @var ActionDimension[] $performanceDimensions */
        $performanceDimensions = [
            new TimeNetwork(),
            new TimeServer(),
            new TimeTransfer(),
            new TimeDomProcessing(),
            new TimeDomCompletion(),
            new TimeOnLoad()
        ];

        $valuesToUpdate = [];

        foreach ($performanceDimensions as $performanceDimension) {
            $paramValue = $request->getParam($performanceDimension->getRequestParam());
            if ($paramValue > -1) {
                $valuesToUpdate[$performanceDimension->getColumnName()] = $paramValue;
            }
        }

        if (empty($valuesToUpdate)) {
            return; // no values to update given with the request
        }

        $idLinkVa = $this->getPageViewId($idVisit, $pageViewId);

        // ignore requests
        if (empty($idLinkVa)) {
            return;
        }

        Log::info('Updating page performance metrics of page view with id ' . $pageViewId);

        $model = new Model();
        $model->updateAction($idLinkVa, $valuesToUpdate);
    }

    protected function getPageViewId($idVisit, $pageViewId)
    {
        $query = sprintf(
            'SELECT idlink_va FROM %1$s LEFT JOIN %2$s ON idaction_url = idaction WHERE idvisit = ? AND idpageview = ? AND %2$s.type = ?',
            Common::prefixTable('log_link_visit_action'),
            Common::prefixTable('log_action')
        );

        $idLinkVa = Tracker::getDatabase()->fetchOne($query, [$idVisit, $pageViewId, Action::TYPE_PAGE_URL]);

        if (!empty($idLinkVa)) {
            return $idLinkVa;
        }

        $query = sprintf(
            'SELECT idlink_va FROM %1$s LEFT JOIN %2$s ON idaction_name = idaction WHERE idvisit = ? AND idpageview = ? AND %2$s.type = ?',
            Common::prefixTable('log_link_visit_action'),
            Common::prefixTable('log_action')
        );

        return Tracker::getDatabase()->fetchOne($query, [$idVisit, $pageViewId, Action::TYPE_PAGE_TITLE]);
    }
}
