<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitTotalSearches extends VisitDimension
{
    protected $columnName = 'visit_total_searches';
    protected $columnType = 'SMALLINT(5) UNSIGNED NULL';
    protected $segmentName = 'searches';
    protected $nameSingular = 'General_NbSearches';
    protected $acceptValues = 'To select all visits who used internal Site Search, use: &segment=searches>0';
    protected $type = self::TYPE_NUMBER;

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        if ($this->isSiteSearchAction($action)) {
            return 1;
        }

        return 0;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if ($this->isSiteSearchAction($action)) {
            return 'visit_total_searches + 1';
        }

        return false;
    }

    /**
     * @param Action|null $action
     * @return bool
     */
    private function isSiteSearchAction($action)
    {
        return ($action && $action->getActionType() == Action::TYPE_SITE_SEARCH);
    }

}