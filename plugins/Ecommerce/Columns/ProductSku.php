<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\Dimension;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Tracker\Action;

class ProductSku extends Dimension
{
    protected $type = self::TYPE_JOIN_ID;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'idaction_sku';
    protected $nameSingular = 'Goals_ProductSKU';
    protected $category = 'Goals_Ecommerce';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin(Action::TYPE_ECOMMERCE_ITEM_SKU);
    }
}