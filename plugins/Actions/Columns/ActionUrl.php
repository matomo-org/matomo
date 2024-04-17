<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Actions\Segment;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\TableLogAction;

class ActionUrl extends ActionDimension
{
    protected $nameSingular = 'Actions_ColumnActionURL';
    protected $columnName = 'idaction_url';
    protected $type = self::TYPE_URL;
    protected $sqlFilter = [TableLogAction::class, 'getOptimizedIdActionSqlMatch'];

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $segment = new Segment();
        $segment->setSegment('actionUrl');
        $segment->setName('Actions_ColumnActionURL');
        $segment->setUnionOfSegments(array('pageUrl', 'downloadUrl', 'outlinkUrl', 'eventUrl'));

        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }
}
