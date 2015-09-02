<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Exception;
use Piwik\Plugin;
use Piwik\Plugin\ComponentFactory;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;

/**
 * @api
 * @since 2.5.0
 */
abstract class Dimension
{
    const COMPONENT_SUBNAMESPACE = 'Columns';

    // TODO that we have quite a few @ignore in public methods might show we should maybe split some code into two
    // classes.

    /**
     * This will be the name of the column in the database table if a $columnType is specified.
     * @var string
     * @api
     */
    protected $columnName = '';

    /**
     * If a columnType is defined, we will create a column in the MySQL table having this type. Please make sure
     * MySQL understands this type. Once you change the column type the Piwik platform will notify the user to
     * perform an update which can sometimes take a long time so be careful when choosing the correct column type.
     * @var string
     * @api
     */
    protected $columnType = '';

    /**
     * Holds an array of segment instances
     * @var Segment[]
     */
    protected $segments = array();

    /**
     * Overwrite this method to configure segments. To do so just create an instance of a {@link \Piwik\Plugin\Segment}
     * class, configure it and call the {@link addSegment()} method. You can add one or more segments for this
     * dimension. Example:
     *
     * ```
     * $segment = new Segment();
     * $segment->setSegment('exitPageUrl');
     * $segment->setName('Actions_ColumnExitPageURL');
     * $segment->setCategory('General_Visit');
     * $this->addSegment($segment);
     * ```
     */
    protected function configureSegments()
    {
    }

    /**
     * Check whether a dimension has overwritten a specific method.
     * @param $method
     * @return bool
     * @ignore
     */
    public function hasImplementedEvent($method)
    {
        $method = new \ReflectionMethod($this, $method);
        $declaringClass = $method->getDeclaringClass();

        return 0 === strpos($declaringClass->name, 'Piwik\Plugins');
    }

    /**
     * Adds a new segment. The segment type will be set to 'dimension' automatically if not already set.
     * @param Segment $segment
     * @api
     */
    protected function addSegment(Segment $segment)
    {
        $type = $segment->getType();

        if (empty($type)) {
            $segment->setType(Segment::TYPE_DIMENSION);
        }

        $this->segments[] = $segment;
    }

    /**
     * Get the list of configured segments.
     * @return Segment[]
     * @ignore
     */
    public function getSegments()
    {
        if (empty($this->segments)) {
            $this->configureSegments();
        }

        return $this->segments;
    }

    /**
     * Get the name of the dimension column.
     * @return string
     * @ignore
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Check whether the dimension has a column type configured
     * @return bool
     * @ignore
     */
    public function hasColumnType()
    {
        return !empty($this->columnType);
    }

    /**
     * Get the translated name of the dimension. Defaults to an empty string.
     * @return string
     * @api
     */
    public function getName()
    {
        return '';
    }

    /**
     * Returns a unique string ID for this dimension. The ID is built using the namespaced class name
     * of the dimension, but is modified to be more human readable.
     *
     * @return string eg, `"Referrers.Keywords"`
     * @throws Exception if the plugin and simple class name of this instance cannot be determined.
     *                   This would only happen if the dimension is located in the wrong directory.
     * @api
     */
    final public function getId()
    {
        $className = get_class($this);

        // parse plugin name & dimension name
        $regex = "/Piwik\\\\Plugins\\\\([^\\\\]+)\\\\" . self::COMPONENT_SUBNAMESPACE . "\\\\([^\\\\]+)/";
        if (!preg_match($regex, $className, $matches)) {
            throw new Exception("'$className' is located in the wrong directory.");
        }

        $pluginName = $matches[1];
        $dimensionName = $matches[2];

        return $pluginName . '.' . $dimensionName;
    }

    /**
     * Gets an instance of all available visit, action and conversion dimension.
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

    public static function getDimensions(Plugin $plugin)
    {
        $dimensions = array();

        foreach (VisitDimension::getDimensions($plugin) as $dimension) {
            $dimensions[] = $dimension;
        }

        foreach (ActionDimension::getDimensions($plugin) as $dimension) {
            $dimensions[] = $dimension;
        }

        foreach (ConversionDimension::getDimensions($plugin) as $dimension) {
            $dimensions[] = $dimension;
        }

        return $dimensions;
    }

    /**
     * Creates a Dimension instance from a string ID (see {@link getId()}).
     *
     * @param string $dimensionId See {@link getId()}.
     * @return Dimension|null The created instance or null if there is no Dimension for
     *                        $dimensionId or if the plugin that contains the Dimension is
     *                        not loaded.
     * @api
     */
    public static function factory($dimensionId)
    {
        list($module, $dimension) = explode('.', $dimensionId);
        return ComponentFactory::factory($module, $dimension, __CLASS__);
    }

    /**
     * Returns the name of the plugin that contains this Dimension.
     *
     * @return string
     * @throws Exception if the Dimension is not located within a Plugin module.
     * @api
     */
    public function getModule()
    {
        $id = $this->getId();
        if (empty($id)) {
            throw new Exception("Invalid dimension ID: '$id'.");
        }

        $parts = explode('.', $id);
        return reset($parts);
    }
}
