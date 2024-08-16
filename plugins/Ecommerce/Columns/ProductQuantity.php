<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\Columns;

class ProductQuantity extends BaseProduct
{
    protected $type = self::TYPE_NUMBER;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'quantity';
    protected $nameSingular = 'Goals_ProductQuantity';
    protected $category = 'Goals_Ecommerce';
    protected $baseProductSingular = 'General_Quantity';
}
