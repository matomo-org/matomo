<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Common;
use Piwik\Metrics\Formatter;
use Matomo\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

/**
 * Dimension for the log_visit.location_ip column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitIp extends VisitDimension
{
    protected $columnName = 'location_ip';
    protected $type = self::TYPE_BINARY;
    protected $allowAnonymous = false;
    protected $segmentName = 'visitIp';
    protected $nameSingular = 'General_VisitorIP';
    protected $namePlural = 'General_VisitorIPs';
    protected $sqlFilterValue = array('Matomo\Network\IPUtils', 'stringToBinaryIP');

    /**
     * The IP is set once, when the visit starts. The exception is the request that brings consent
     * into a visit measured without it: the IP stored for that visit was anonymised as the visit's
     * lack of consent required, and the visit is the consented one from here on.
     *
     * The value written is the one {@see \Piwik\Plugins\CoreHome\Tracker\VisitRequestProcessor}
     * already prepared for this request, anonymised as the consent allows.
     *
     * @param Action|null $action
     * @return string|false
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if (!Consent::isBroughtIntoVisitBy($request, $visitor, $action)) {
            return false;
        }

        return $visitor->getVisitorColumn($this->columnName);
    }

    public function getAcceptValues()
    {
        return Piwik::translate('General_VisitorIPSegmentHelp', ['13.54.122.1', '<code>visitIp>13.54.122.0;visitIp<13.54.122.255</code>']);
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        $value = Common::hex2bin($value);
        $value = IPUtils::binaryToStringIP($value);
        return $value;
    }

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $segment = new Segment();
        $segment->setType(Segment::TYPE_METRIC); // we cannot remove this for now as it would assign dimension based on text type
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }
}
