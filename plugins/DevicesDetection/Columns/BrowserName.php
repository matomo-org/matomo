<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use DeviceDetector\Parser\Client\Browser;
use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Segment;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class BrowserName extends Base
{
    protected $columnName = 'config_browser_name';
    protected $columnType = 'VARCHAR(40) NULL';
    protected $segmentName = 'browserCode';
    protected $nameSingular = 'DevicesDetection_ColumnBrowser';
    protected $namePlural = 'DevicesDetection_Browsers';
    protected $acceptValues = 'FF, IE, CH, SF, OP etc.';
    protected $type = self::TYPE_TEXT;

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $segment = new Segment();
        $segment->setName('DevicesDetection_BrowserCode');
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));

        $segment = new Segment();
        $segment->setSegment('browserName');
        $segment->setName('DevicesDetection_ColumnBrowser');
        $segment->setAcceptedValues('FireFox, Internet Explorer, Chrome, Safari, Opera etc.');
        $segment->setNeedsMostFrequentValues(false);
        $segment->setSqlFilterValue(function ($val) {
            $browsers = Browser::getAvailableBrowsers();
            $browsers = array_map(function($val) {
                return mb_strtolower($val);
            }, $browsers);
            $result   = array_search(mb_strtolower($val), $browsers);

            if ($result === false) {
                $result = 'UNK';
            }

            return $result;
        });
        $segment->setSuggestedValuesCallback(function ($idSite, $maxValuesToReturn, $table) {
            $browserList = Browser::getAvailableBrowsers();
            return $this->sortStaticListByUsage($browserList, $table, 'browserCode', $maxValuesToReturn);
        });
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\DevicesDetection\getBrowserName($value);
    }

    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $parser    = $this->getUAParser($request->getUserAgent(), $request->getClientHints());

        $aBrowserInfo = $parser->getClient();

        if (!empty($aBrowserInfo['short_name'])) {

            return $aBrowserInfo['short_name'];
        } else if (!empty($aBrowserInfo['name'])) {

            return $aBrowserInfo['name'];
        }

        return 'UNK';
    }
}
