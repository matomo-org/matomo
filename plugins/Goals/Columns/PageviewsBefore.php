<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Columns;

use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Tracker\ActionPageview;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;

class PageviewsBefore extends ConversionDimension
{
    protected $columnName = 'pageviews_before';
    protected $columnType = 'SMALLINT UNSIGNED DEFAULT NULL';
    protected $type = self::TYPE_NUMBER;
    protected $category = 'Goals_Goals';
    protected $nameSingular = 'Goals_PageviewsBefore';

    /**
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return int
     */
    public function onEcommerceOrderConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        return $this->onGoalConversion($request, $visitor, $action, $goalManager);
    }

    /**
     *
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return int
     */
    public function onGoalConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        // The visit total interactions are incremented and stored on the visit but include searches
        // To get the current number of pageviews for the visit we subtract the total searches from the total interactions

        $visitPageviews = $visitor->getImmutableVisitorColumn('visit_total_interactions') -
                          $visitor->getImmutableVisitorColumn('visit_total_searches');

        if ($action instanceof ActionPageview) {
            $visitPageviews++; // The current action isn't yet included in visit total interactions
        }

        return $visitPageviews;
    }

}
