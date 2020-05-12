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

class ProductPrice extends Dimension
{
    protected $type = self::TYPE_MONEY;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'price';
    protected $nameSingular = 'Goals_ProductPrice';
    protected $category = 'Goals_Ecommerce';
    protected $segmentName = 'productPrice';

}