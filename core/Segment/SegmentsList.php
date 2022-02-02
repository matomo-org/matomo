<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Segment;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Columns\Dimension;
use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Piwik;
use Piwik\Plugin\Segment;

/**
 * Manages the global list of segments that can be used.
 *
 * Segments are added automatically by dimensions as well as through the {@hook Segment.addSegments} event.
 * Observers for this event should call the {@link addSegment()} method to add segments or use any of the other
 * methods to remove segments.
 *
 * @api since Piwik 4.0.0
 */
class SegmentsList
{
    /**
     * List of segments
     *
     * @var Segment[]
     */
    private $segments = array();

    private $segmentsByNameCache = array();

    /**
     * @param Segment $segment
     */
    public function addSegment(Segment $segment)
    {
        $this->segments[]          = $segment;
        $this->segmentsByNameCache = array();
    }

    /**
     * Get all available segments.
     *
     * @return Segment[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Removes one or more segments from the segments list.
     *
     * @param string       $segmentCategory   The segment category id. Can be a translation token eg 'General_Visits'
     *                                        see {@link Segment::getCategoryId()}.
     * @param string|false $segmentExpression The segment expression name to remove eg 'pageUrl'.
     *                                        If not supplied, all segments within that category will be removed.
     */
    public function remove($segmentCategory, $segmentExpression = false)
    {
        foreach ($this->segments as $index => $segment) {
            if ($segment->getCategoryId() === $segmentCategory) {
                if (!$segmentExpression || $segment->getSegment() === $segmentExpression) {
                    unset($this->segments[$index]);
                    $this->segmentsByNameCache = array();
                }
            }
        }
    }

    /**
     * @param string $segmentExpression Name of the segment expression. eg `pageUrl`
     * @return Segment|null
     */
    public function getSegment($segmentExpression)
    {
        if (empty($this->segmentsByNameCache)) {
            foreach ($this->segments as $index => $segment) {
                $this->segmentsByNameCache[$segment->getSegment()] = $segment;
            }
        }

        if (!empty($this->segmentsByNameCache[$segmentExpression])) {
            return $this->segmentsByNameCache[$segmentExpression];
        }

        return null;
    }

    /**
     * Get all metrics defined in the Piwik platform.
     * @ignore
     * @return static
     */
    public static function get()
    {
        $cache = Cache::getTransientCache();
        $cacheKey = CacheId::siteAware('SegmentsList');

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        $list = new static;

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
         * @param SegmentsList $list An instance of the SegmentsList. You can add segments to the list this way.
         */
        Piwik::postEvent('Segment.addSegments', array($list));

        foreach (Dimension::getAllDimensions() as $dimension) {
            $dimension->configureSegments($list, new DimensionSegmentFactory($dimension));
        }

        /**
         * Triggered to filter segment definitions.
         *
         * **Example**
         *
         *     public function filterSegments(&$segmentList)
         *     {
         *         $segmentList->remove('Category');
         *     }
         *
         * @param SegmentsList $list An instance of the SegmentsList.
         */
        Piwik::postEvent('Segment.filterSegments', array($list));
        
        $cache->save($cacheKey, $list);

        return $list;
    }

}
