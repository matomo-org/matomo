<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\Columns\Dimension;
use Piwik\Piwik;

class SegmentMetadata
{
    public function getSegmentsMetadata($idSites = array(), $_hideImplementationData = true, $isAuthenticatedWithViewAccess)
    {
        $segments = array();

        foreach (Dimension::getAllDimensions() as $dimension) {
            foreach ($dimension->getSegments() as $segment) {
                if ($segment->isRequiresAtLeastViewAccess()) {
                    $segment->setPermission($isAuthenticatedWithViewAccess);
                }

                $segments[] = $segment->toArray();
            }
        }

        /**
         * Triggered when gathering all available segment dimensions.
         *
         * This event can be used to make new segment dimensions available.
         *
         * **Example**
         *
         *     public function getSegmentsMetadata(&$segments, $idSites)
         *     {
         *         $segments[] = array(
         *             'type'           => 'dimension',
         *             'category'       => Piwik::translate('General_Visit'),
         *             'name'           => 'General_VisitorIP',
         *             'segment'        => 'visitIp',
         *             'acceptedValues' => '13.54.122.1, etc.',
         *             'sqlSegment'     => 'log_visit.location_ip',
         *             'sqlFilter'      => array('Piwik\IP', 'P2N'),
         *             'permission'     => $isAuthenticatedWithViewAccess,
         *         );
         *     }
         *
         * @param array &$dimensions The list of available segment dimensions. Append to this list to add
         *                           new segments. Each element in this list must contain the
         *                           following information:
         *
         *                           - **type**: Either `'metric'` or `'dimension'`. `'metric'` means
         *                                       the value is a numeric and `'dimension'` means it is
         *                                       a string. Also, `'metric'` values will be displayed
         *                                       under **Visit (metrics)** in the Segment Editor.
         *                           - **category**: The segment category name. This can be an existing
         *                                           segment category visible in the segment editor.
         *                           - **name**: The pretty name of the segment. Can be a translation token.
         *                           - **segment**: The segment name, eg, `'visitIp'` or `'searches'`.
         *                           - **acceptedValues**: A string describing one or two exacmple values, eg
         *                                                 `'13.54.122.1, etc.'`.
         *                           - **sqlSegment**: The table column this segment will segment by.
         *                                             For example, `'log_visit.location_ip'` for the
         *                                             **visitIp** segment.
         *                           - **sqlFilter**: A PHP callback to apply to segment values before
         *                                            they are used in SQL.
         *                           - **permission**: True if the current user has view access to this
         *                                             segment, false if otherwise.
         * @param array $idSites The list of site IDs we're getting the available segments
         *                       for. Some segments (such as Goal segments) depend on the
         *                       site.
         */
        Piwik::postEvent('API.getSegmentDimensionMetadata', array(&$segments, $idSites));

        foreach ($segments as &$segment) {
            $segment['name'] = Piwik::translate($segment['name']);
            $segment['category'] = Piwik::translate($segment['category']);

            if ($_hideImplementationData) {
                unset($segment['sqlFilter']);
                unset($segment['sqlFilterValue']);
                unset($segment['sqlSegment']);
            }
        }

        usort($segments, array($this, 'sortSegments'));

        return $segments;
    }

    private function sortSegments($row1, $row2)
    {
        $customVarCategory = Piwik::translate('CustomVariables_CustomVariables');

        $columns = array('type', 'category', 'name', 'segment');

        foreach ($columns as $column) {
            // Keep segments ordered alphabetically inside categories..
            $type = -1;
            if ($column == 'name') $type = 1;

            $compare = $type * strcmp($row1[$column], $row2[$column]);

            // hack so that custom variables "page" are grouped together in the doc
            if ($row1['category'] == $customVarCategory
                && $row1['category'] == $row2['category']
            ) {
                $compare = strcmp($row1['segment'], $row2['segment']);
                return $compare;
            }

            if ($compare != 0) {
                return $compare;
            }
        }

        return $compare;
    }

}