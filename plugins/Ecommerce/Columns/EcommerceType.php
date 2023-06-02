<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\Dimension;
use Piwik\Tracker\GoalManager;

class EcommerceType extends Dimension
{
    const TYPE_ABANDONED_CART = 'abandonedCart';
    const TYPE_ORDER = 'order';

    protected $type = self::TYPE_NUMBER;
    protected $dbTableName = 'log_conversion_item';
    protected $nameSingular = 'Goals_EcommerceType';
    protected $category = 'Goals_Ecommerce';
    protected $acceptValues = 'Either "abandonedCart" or "order".';

    public function __construct()
    {
        $this->sqlSegment = sprintf(
            'CASE log_conversion_item.idorder WHEN \'0\' THEN %d ELSE %d END',
            GoalManager::IDGOAL_CART,
            GoalManager::IDGOAL_ORDER
        );

        $this->suggestedValuesCallback = function () {
            return [self::TYPE_ABANDONED_CART, self::TYPE_ORDER];
        };

        $this->sqlFilterValue = function ($value) {
            if ($value == self::TYPE_ABANDONED_CART) {
                return GoalManager::IDGOAL_ORDER;
            }

            if ($value == self::TYPE_ORDER) {
                return GoalManager::IDGOAL_CART;
            }

            return null;
        };
    }
}
