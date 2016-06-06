<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\API\Request;
use Piwik\Config;
use Piwik\Db;

/**
 */
class SegmentList
{
    public function findSegment($segmentName, $idSite)
    {
        $segments = Request::processRequest('API.getSegmentsMetadata', array(
            'idSites' => array($idSite),
        ));

        foreach ($segments as $segment) {
            if ($segment['segment'] == $segmentName && !empty($segmentName)) {
                return $segment;
            }
        }
    }

}
