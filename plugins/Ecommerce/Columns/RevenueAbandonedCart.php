<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

class RevenueAbandonedCart extends BaseConversion
{
    protected $columnName = 'revenue';
    protected $columnType = 'float default NULL';
    protected $type = self::TYPE_MONEY;
    protected $category = 'Goals_Ecommerce';
    protected $nameSingular = 'Ecommerce_RevenueLeftInCart';
    protected $segmentName = 'revenueAbandonedCart';

}