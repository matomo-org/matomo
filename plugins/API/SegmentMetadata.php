<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\Category\CategoryList;
use Piwik\Columns\Dimension;
use Piwik\Piwik;
use Piwik\Plugin\Segment;

class SegmentMetadata
{
    /**
     * Map of category name to order
     * @var array
     */
    private $categoryOrder = array();

    public function getSegmentsMetadata($idSites = array(), $_hideImplementationData = true, $isAuthenticatedWithViewAccess)
    {
        $segments = array();

        /**
         * Triggered to add custom segment definitions.
         *
         * **Example**
         *
         *     public function addSegments(&$segments)
         *     {
         *         $segment = new Segment();
         *         $segment->setSegment('my_segment_name');
         *         $segment->setType(Segment::TYPE_DIMENSION);
         *         $segment->setName('My Segment Name');
         *         $segment->setSqlSegment('log_table.my_segment_name');
         *         $segments[] = $segment;
         *     }
         *
         * @param array &$segments An array containing a list of segment entries.
         */
        Piwik::postEvent('Segment.addSegments', array(&$segments));

        foreach (Dimension::getAllDimensions() as $dimension) {
            foreach ($dimension->getSegments() as $segment) {
                $segments[] = $segment;
            }
        }

        /** @var Segment[] $dimensionSegments */
        $dimensionSegments = $segments;
        $segments = array();

        foreach ($dimensionSegments as $segment) {
            if ($segment->isRequiresAtLeastViewAccess()) {
                $segment->setPermission($isAuthenticatedWithViewAccess);
            }

            $segments[] = $segment->toArray();
        }

        $categoryList = CategoryList::get();

        foreach ($segments as &$segment) {
            $categoryId = $segment['category'];
            $segment['name'] = Piwik::translate($segment['name']);
            $segment['category'] = Piwik::translate($categoryId);

            if (!isset($this->categoryOrder[$segment['category']])) {
                $category = $categoryList->getCategory($categoryId);
                if (!empty($category)) {
                    $this->categoryOrder[$segment['category']] = $category->getOrder();
                } else {
                    $this->categoryOrder[$segment['category']] = 999;
                }
            }

            if ($_hideImplementationData) {
                unset($segment['sqlFilter']);
                unset($segment['sqlFilterValue']);
                unset($segment['sqlSegment']);

                if (isset($segment['suggestedValuesCallback'])
                    && !is_string($segment['suggestedValuesCallback'])
                ) {
                    unset($segment['suggestedValuesCallback']);
                }
            }
        }

        usort($segments, array($this, 'sortSegments'));

        return $segments;
    }

    private function sortSegments($row1, $row2)
    {
        $customVarCategory = Piwik::translate('CustomVariables_CustomVariables');

        $columns = array('category', 'type', 'name', 'segment');

        foreach ($columns as $column) {
            // Keep segments ordered alphabetically inside categories..
            $type = -1;
            if ($column == 'name') {
                $type = 1;
            }

            if ($column === 'category') {
                $idOrder1 = $this->categoryOrder[$row1[$column]];
                $idOrder2 = $this->categoryOrder[$row2[$column]];

                if ($idOrder1 === $idOrder2) {
                    continue;
                }

                return $idOrder1 > $idOrder2 ? 1 : -1;
            }

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
