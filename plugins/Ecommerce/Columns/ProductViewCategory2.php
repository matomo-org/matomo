<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Segment\SegmentsList;

class ProductViewCategory2 extends ProductViewCategory
{
    protected $columnName = 'idaction_product_cat2';
    protected $categoryNumber = 2;

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        // handled in category 1
    }
}