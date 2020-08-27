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
use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Tracker\Action;

class ProductSku extends Dimension
{
    protected $type = self::TYPE_TEXT;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'idaction_sku';
    protected $nameSingular = 'Goals_ProductSKU';
    protected $namePlural = 'Goals_ProductSKUs';
    protected $category = 'Goals_Ecommerce';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';
    protected $segmentName = 'productSku';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_ECOMMERCE_ITEM_SKU);
    }

}