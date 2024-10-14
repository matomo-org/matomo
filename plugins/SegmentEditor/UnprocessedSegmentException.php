<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\Piwik;
use Piwik\Segment;

class UnprocessedSegmentException extends \Exception
{
    /**
     * @var Segment
     */
    private $segment;

    /**
     * @var array|null
     */
    private $storedSegment;

    /**
     * @var bool
     */
    private $isSegmentToPreprocess;

    /**
     * @param $segment
     */
    public function __construct(Segment $segment, $isSegmentToPreprocess, ?array $storedSegment = null)
    {
        parent::__construct(self::getErrorMessage($segment, $isSegmentToPreprocess, $storedSegment));

        $this->segment = $segment;
        $this->storedSegment = $storedSegment;
        $this->isSegmentToPreprocess = $isSegmentToPreprocess;
    }

    /**
     * @return Segment
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * @return array|null
     */
    public function getStoredSegment()
    {
        return $this->storedSegment;
    }

    private static function getErrorMessage(Segment $segment, $isSegmentToPreprocess, ?array $storedSegment = null)
    {
        if (empty($storedSegment)) {
            // the segment was not created through the segment editor
            return Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError1')
                . ' ' . Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError2')
                . ' ' . Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError3')
                . ' ' . Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError4')
                . ' ' . Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError5')
                . ' ' . Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError6')
                . ' ' . Piwik::translate('SegmentEditor_UnprocessedSegmentInVisitorLog3');
        }

        $segmentName = !empty($storedSegment['name']) ? $storedSegment['name'] : $segment->getString();

        if (!$isSegmentToPreprocess) {
            // the segment was created in the segment editor, but set to be processed in real time
            return Piwik::translate('SegmentEditor_UnprocessedSegmentApiError1', [$segmentName, Piwik::translate('SegmentEditor_AutoArchiveRealTime')])
                . ' ' . Piwik::translate('SegmentEditor_UnprocessedSegmentApiError2', [Piwik::translate('SegmentEditor_AutoArchivePreProcessed')])
                . ' ' . Piwik::translate('SegmentEditor_UnprocessedSegmentApiError3');
        }

        // the segment is set to be processed during cron archiving, but has not been processed yet
        return Piwik::translate('SegmentEditor_UnprocessedSegmentNoData1', ['(' . $segmentName . ')'])
                . ' ' . Piwik::translate('SegmentEditor_UnprocessedSegmentNoData2')
                . ' ' . Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError5')
                . ' ' . Piwik::translate('SegmentEditor_CustomUnprocessedSegmentApiError6')
                . ' ' . Piwik::translate('SegmentEditor_UnprocessedSegmentInVisitorLog3');
    }

    /**
     * @return bool
     */
    public function isSegmentToPreprocess()
    {
        return $this->isSegmentToPreprocess;
    }
}
