<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\DataAccess\LogQueryBuilder;
use Piwik\Plugin\LogTablesProvider;
use Piwik\Plugins\SegmentEditor\Services\StoredSegmentService;
use Piwik\Segment\SegmentExpression;
use Piwik\SettingsServer;

/**
 * Decorates segment sub-queries in archiving queries w/ the idSegment of the segment, if
 * a stored segment exists.
 *
 * This class is configured for use in SegmentEditor's DI config.
 */
class SegmentQueryDecorator extends LogQueryBuilder
{
    /**
     * @var StoredSegmentService
     */
    private $storedSegmentService;

    public function __construct(StoredSegmentService $storedSegmentService, LogTablesProvider $logTablesProvider)
    {
        $this->storedSegmentService = $storedSegmentService;
        parent::__construct($logTablesProvider);
    }

    public function getSelectQueryString(SegmentExpression $segmentExpression, $select, $from, $where, $bind, $groupBy,
                                         $orderBy, $limit)
    {
        $result = parent::getSelectQueryString($segmentExpression, $select, $from, $where, $bind, $groupBy, $orderBy,
            $limit);

        $prefixParts = array();

        if (SettingsServer::isArchivePhpTriggered()) {
            $prefixParts[] = 'trigger = CronArchive';
        }

        $idSegments = $this->getSegmentIdOfExpression($segmentExpression);
        if (!empty($idSegments)) {
            $prefixParts[] = "idSegments = [" . implode(', ', $idSegments) . "]";
        }

        $select = 'SELECT';
        if (!empty($prefixParts) && 0 === strpos(trim($result['sql']), $select)) {
            $result['sql'] = trim($result['sql']);
            $result['sql'] = 'SELECT /* ' . implode(', ', $prefixParts) . ' */' . substr($result['sql'], strlen($select));
        }

        return $result;
    }

    private function getSegmentIdOfExpression(SegmentExpression $segmentExpression)
    {
        $allSegments = $this->storedSegmentService->getAllSegmentsAndIgnoreVisibility();

        $idSegments = array();
        foreach ($allSegments as $segment) {
            if ($segmentExpression->getSegmentDefinition() == $segment['definition']) {
                $idSegments[] = $segment['idsegment'];
            }
        }
        return $idSegments;
    }
}
