<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\Access;
use Piwik\DataAccess\LogQueryBuilder;
use Piwik\Segment\SegmentExpression;

/**
 * Decorates segment sub-queries in archiving queries w/ the idSegment of the segment, if
 * a stored segment exists.
 *
 * This class is configured for use in SegmentEditor's DI config.
 */
class SegmentQueryDecorator extends LogQueryBuilder
{
    /**
     * @var API
     */
    private $segmentEditorApi;

    public function __construct(API $segmentEditorApi)
    {
        $this->segmentEditorApi = $segmentEditorApi;
    }

    public function getSelectQueryString(SegmentExpression $segmentExpression, $select, $from, $where, $bind, $groupBy,
                                         $orderBy, $limit)
    {
        $result = parent::getSelectQueryString($segmentExpression, $select, $from, $where, $bind, $groupBy, $orderBy,
            $limit);

        $idSegments = $this->getSegmentIdOfExpression($segmentExpression);
        if (!empty($idSegments)) {
            $result['sql'] = "/* idSegments = [" . implode(', ', $idSegments) . "] */\n" . $result['sql'];
        }

        return $result;
    }

    private function getSegmentIdOfExpression(SegmentExpression $segmentExpression)
    {
        $segmentEditorApi = $this->segmentEditorApi;
        $allSegments = Access::doAsSuperUser(function () use ($segmentEditorApi) {
            return $segmentEditorApi->getSegmentsToAutoArchive('all');
        });

        $idSegments = array();
        foreach ($allSegments as $segment) {
            if ($segmentExpression->getSegmentDefinition() == $segment['definition']) {
                $idSegments[] = $segment['idsegment'];
            }
        }
        return $idSegments;
    }
}
