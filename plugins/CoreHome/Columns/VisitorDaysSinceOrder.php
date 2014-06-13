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

class VisitorDaysSinceOrder extends VisitDimension
{
    protected $fieldName = 'visitor_days_since_order';
    protected $fieldType = 'SMALLINT(5) UNSIGNED NOT NULL';

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
        $daysSinceLastOrder = $request->getDaysSinceLastOrder();

        if ($daysSinceLastOrder === false) {
            $daysSinceLastOrder = 0;
        }

        return $daysSinceLastOrder;
    }
}