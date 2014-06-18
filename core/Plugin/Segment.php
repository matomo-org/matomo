<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

/**
 * @api
 * @since 2.5.0
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
    private $permission;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {

    }

    /**
     * @param string $acceptValues
     */
    public function setAcceptedValues($acceptValues)
    {
        $this->acceptValues = $acceptValues;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $segment
     */
    public function setSegment($segment)
    {
        $this->segment = $segment;
    }

    /**
     * @param string|\Closure $sqlFilter
     */
    public function setSqlFilter($sqlFilter)
    {
        $this->sqlFilter = $sqlFilter;
    }

    /**
     * @param string|array $sqlFilterValue
     */
    public function setSqlFilterValue($sqlFilterValue)
    {
        $this->sqlFilterValue = $sqlFilterValue;
    }

    /**
     * @param string $sqlSegment
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
     * @param string $type See constansts TYPE_*
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param bool $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
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

        if (isset($this->permission)) {
            $segment['permission'] = $this->permission;
        }

        return $segment;
    }
}
