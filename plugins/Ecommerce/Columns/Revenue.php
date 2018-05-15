<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Tracker\GoalManager;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class Revenue extends BaseConversion
{
    protected $columnName = 'revenue';
    protected $columnType = 'float default NULL';
    protected $type = self::TYPE_MONEY;
    protected $category = 'Goals_Ecommerce';
    protected $nameSingular = 'Ecommerce_OrderValue';

    public function getDbDiscriminator()
    {
        return new Discriminator($this->dbTableName, 'idgoal', GoalManager::IDGOAL_ORDER);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     */
    public function onGoalConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        $defaultRevenue = $goalManager->getGoalColumn('revenue');
        $revenue        = $request->getGoalRevenue($defaultRevenue);

        return $this->roundRevenueIfNeeded($revenue);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     */
    public function onEcommerceOrderConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        $defaultRevenue = 0;
        $revenue = $request->getGoalRevenue($defaultRevenue);

        return $this->roundRevenueIfNeeded($revenue);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @param GoalManager $goalManager
     *
     * @return mixed|false
     */
    public function onEcommerceCartUpdateConversion(Request $request, Visitor $visitor, $action, GoalManager $goalManager)
    {
        return $this->onEcommerceOrderConversion($request, $visitor, $action, $goalManager);
    }
}