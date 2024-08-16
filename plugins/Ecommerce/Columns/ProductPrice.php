<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\Columns;

class ProductPrice extends BaseProduct
{
    protected $type = self::TYPE_MONEY;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'price';
    protected $nameSingular = 'Goals_ProductPrice';
    protected $category = 'Goals_Ecommerce';
    protected $segmentName = 'productPrice';
}
