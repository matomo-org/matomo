<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\Category\CategoryList;
use Piwik\Piwik;
use Piwik\Plugin\Segment;
use Piwik\Segment\SegmentsList;

class SegmentMetadata
{
    /**
     * Map of category name to order
     * @var array
     */
    private $categoryOrder = array();

    public function getSegmentsMetadata($idSites, $_hideImplementationData, $isRegisteredUser, $_showAllSegments = false)
    {
        /** @var Segment[] $dimensionSegments */
        $dimensionSegments = SegmentsList::get()->getSegments();
        $segments = array();

        foreach ($dimensionSegments as $segment) {
            if (!$_showAllSegments
                && $segment->isInternal()
            ) {
                continue;
            }

            if ($segment->isRequiresRegisteredUser()) {
                $segment->setPermission($isRegisteredUser);
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
                    $segment['category'] = $category->getDisplayName();
                    $this->categoryOrder[$segment['category']] = $category->getOrder();
                } else {
                    $this->categoryOrder[$segment['category']] = 999;
                }
            }

            if ($_hideImplementationData) {
                unset($segment['sqlFilter']);
                unset($segment['sqlFilterValue']);
                unset($segment['sqlSegment']);
                unset($segment['needsMostFrequentValues']);

                if (isset($segment['suggestedValuesCallback'])
                    && !is_string($segment['suggestedValuesCallback'])
                ) {
                    unset($segment['suggestedValuesCallback']);
                }

                if (isset($segment['suggestedValuesApi'])) {
                    unset($segment['suggestedValuesApi']);
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
