<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Common;
use Piwik\Metrics\Formatter;
use Matomo\Network\IPUtils;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Segment\SegmentsList;

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
    protected $acceptValues = '13.54.122.1. </code>Select IP ranges with notation: <code>visitIp>13.54.122.0;visitIp<13.54.122.255';
    protected $sqlFilterValue = array('Matomo\Network\IPUtils', 'stringToBinaryIP');

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
