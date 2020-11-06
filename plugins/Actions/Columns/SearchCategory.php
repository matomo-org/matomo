<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Segment;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class SearchCategory extends ActionDimension
{
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'Actions_ColumnSearchCategory';
    protected $namePlural = 'Actions_SiteSearchCategories';
    protected $segmentName = 'siteSearchCategory';
    protected $columnName = 'search_cat';
    protected $columnType = 'VARCHAR(200) NULL';

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        if ($action instanceof ActionSiteSearch) {
            return $action->getSearchCategory();
        }

        return parent::onNewAction($request, $visitor, $action);
    }

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $segment = new Segment();
        $segment->setName('Actions_SiteSearchCategory');
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }
}