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
use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Action;

class ProductCategory5 extends Dimension
{
    protected $type = self::TYPE_TEXT;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'idaction_category5';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
    }

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        // no segments
    }
}
