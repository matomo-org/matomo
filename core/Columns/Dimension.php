<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Translate;

/**
 * @api
 * @since 2.5.0
 */
abstract class Dimension
{
    protected $name;
    protected $columnName = '';
    protected $columnType = '';
    protected $segments = array();

    protected function configureSegments()
    {

    }

    public function hasImplementedEvent($method)
    {
        $method = new \ReflectionMethod($this, $method);
        $declaringClass = $method->getDeclaringClass();

        return 0 === strpos($declaringClass->name, 'Piwik\Plugins');
    }

    protected function addSegment(Segment $segment)
    {
        $type = $segment->getType();

        if (empty($type)) {
            $segment->setType(Segment::TYPE_DIMENSION);
        }

        $this->segments[] = $segment;
    }

    /**
     * @return Segment[]
     */
    public function getSegments()
    {
        if (empty($this->segments)) {
            $this->configureSegments();
        }

        return $this->segments;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function hasColumnType()
    {
        return !empty($this->columnType);
    }

    abstract public function getName();

    /**
     * @return Dimension[]
     */
    public static function getAllDimensions()
    {
        $dimensions = array();

        foreach (VisitDimension::getAllDimensions() as $dimension) {
            $dimensions[] = $dimension;
        }

        foreach (ActionDimension::getAllDimensions() as $dimension) {
            $dimensions[] = $dimension;
        }

        foreach (ConversionDimension::getAllDimensions() as $dimension) {
            $dimensions[] = $dimension;
        }

        return $dimensions;
    }
}
