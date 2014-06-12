<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

/**
 * @api
 */
class Segment
{
    const TYPE_DIMENSION = 'dimension';
    const TYPE_METRIC = 'metric';

    private $type;
    private $category;
    private $name;
    private $segment;
    private $sqlSegment;
    private $sqlFilter;
    private $sqlFilterValue;
    private $acceptValues;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {

    }

    /**
     * @param mixed $acceptValues
     */
    public function setAcceptValues($acceptValues)
    {
        $this->acceptValues = $acceptValues;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $segment
     */
    public function setSegment($segment)
    {
        $this->segment = $segment;
    }

    /**
     * @param mixed $sqlFilter
     */
    public function setSqlFilter($sqlFilter)
    {
        $this->sqlFilter = $sqlFilter;
    }

    /**
     * @param mixed $sqlFilterValue
     */
    public function setSqlFilterValue($sqlFilterValue)
    {
        $this->sqlFilterValue = $sqlFilterValue;
    }

    /**
     * @param mixed $sqlSegment
     */
    public function setSqlSegment($sqlSegment)
    {
        $this->sqlSegment = $sqlSegment;
    }

    /**
     * @return string
     */
    public function getSqlSegment()
    {
        return $this->sqlSegment;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function toArray()
    {
        $segment = array(
            'type'       => $this->type,
            'category'   => $this->category,
            'name'       => $this->name,
            'segment'    => $this->segment,
            'sqlSegment' => $this->sqlSegment,
        );

        if (!empty($this->sqlFilter)) {
            $segment['sqlFilter'] = $this->sqlFilter;
        }

        if (!empty($this->sqlFilterValue)) {
            $segment['sqlFilterValue'] = $this->sqlFilterValue;
        }

        if (!empty($this->acceptValues)) {
            $segment['acceptedValues'] = $this->acceptValues;
        }

        return $segment;
    }
}
