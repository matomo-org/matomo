<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use DeviceDetector\Parser\OperatingSystem;
use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Segment;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Request;
use Piwik\Tracker\Settings;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class Os extends Base
{
    protected $columnName = 'config_os';
    protected $columnType = 'CHAR(3) NULL';
    protected $segmentName = 'operatingSystemCode';
    protected $nameSingular = 'DevicesDetection_ColumnOperatingSystem';
    protected $namePlural = 'DevicesDetection_OperatingSystems';
    protected $acceptValues = 'WIN, LIN, MAX, AND, IOS etc.';
    protected $type = self::TYPE_TEXT;

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $segment = new Segment();
        $segment->setName('DevicesDetection_OperatingSystemCode');
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));

        $segment = new Segment();
        $segment->setSegment('operatingSystemName');
        $segment->setName('DevicesDetection_ColumnOperatingSystem');
        $segment->setAcceptedValues('Windows, Linux, Mac, Android, iOS etc.');
        $segment->setNeedsMostFrequentValues(false);
        $segment->setSqlFilterValue(function ($val) {
            $oss = OperatingSystem::getAvailableOperatingSystems();
            $oss = array_map(function($val) {
                return mb_strtolower($val);
            }, $oss);
            $result   = array_search(mb_strtolower($val), $oss);

            if ($result === false) {
                $result = 'UNK';
            }

            return $result;
        });
        $segment->setSuggestedValuesCallback(function ($idSite, $maxValuesToReturn, $table) {
            return $this->sortStaticListByUsage(OperatingSystem::getAvailableOperatingSystems(), $table,
                'operatingSystemCode', $maxValuesToReturn);
        });
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\DevicesDetection\getOSFamilyFullName($value);
    }

    public function getName()
    {
        return Piwik::translate('DevicesDetection_OperatingSystemFamily');
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

        if ($parser->isBot()) {
            $os = Settings::OS_BOT;
        } else {
            $os = $parser->getOS();
            $os = $os['short_name'] ?? 'UNK';
        }

        return $os;
    }
}
