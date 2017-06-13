<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComponentFactory;
use Piwik\Plugin\Metric;
use Piwik\Plugin\Segment;
use Exception;
use Piwik\CacheId;
use Piwik\Cache as PiwikCache;
use Piwik\Plugin\Manager as PluginManager;

/**
 * @api
 * @since 3.1.0
 */
abstract class Dimension
{
    const COMPONENT_SUBNAMESPACE = 'Columns';

    /**
     * Segment type 'dimension'. Can be used along with {@link setType()}.
     * @api
     */
    const TYPE_DIMENSION = 'dimension';
    const TYPE_TEXT = 'text';
    const TYPE_MONEY = 'money';
    const TYPE_DURATION_MS = 'duration_ms';
    const TYPE_DURATION_S = 'duration_s';
    const TYPE_NUMBER = 'number';
    const TYPE_FLOAT = 'float';
    const TYPE_URL = 'url';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_BOOL = 'bool';

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
     * Holds an array of metric instances
     * @var Metric[]
     */
    protected $metrics = array();

    protected $type = '';

    /**
     * Translation key for name singular
     * @var string
     */
    protected $nameSingular = '';
    protected $namePlural = '';

    /**
     * Translation key for category
     * @var string
     */
    protected $category = '';
    protected $segmentName = '';
    protected $suggestedValuesCallback;
    protected $acceptValues;
    protected $sqlFilter;
    protected $sqlFilterValue;
    protected $allowAnonymous;
    protected $dbTableName = '';
    protected $metricId = '';

    public function getMetricId()
    {
        if (!empty($this->metricId)) {
            return $this->metricId;
        }

        return $this->columnName;
    }

    /**
     * Installs the action dimension in case it is not installed yet. The installation is already implemented based on
     * the {@link $columnName} and {@link $columnType}. If you want to perform additional actions beside adding the
     * column to the database - for instance adding an index - you can overwrite this method. We recommend to call
     * this parent method to get the minimum required actions and then add further custom actions since this makes sure
     * the column will be installed correctly. We also recommend to change the default install behavior only if really
     * needed. FYI: We do not directly execute those alter table statements here as we group them together with several
     * other alter table statements do execute those changes in one step which results in a faster installation. The
     * column will be added to the `log_link_visit_action` MySQL table.
     *
     * Example:
     * ```
    public function install()
    {
    $changes = parent::install();
    $changes['log_link_visit_action'][] = "ADD INDEX index_idsite_servertime ( idsite, server_time )";

    return $changes;
    }
    ```
     *
     * @return array An array containing the table name as key and an array of MySQL alter table statements that should
     *               be executed on the given table. Example:
     * ```
    array(
    'log_link_visit_action' => array("ADD COLUMN `$this->columnName` $this->columnType", "ADD INDEX ...")
    );
    ```
     * @api
     */
    public function install()
    {
        if (empty($this->columnName) || empty($this->columnType) || empty($this->dbTableName)) {
            return array();
        }

        // TODO if table does not exist, create it with a primary key, but at this point we cannot really create it
        // cause we need to show the query in the UI first and user needs to be able to create table manually.
        // we cannot return something like "create table " here as it would be returned for each table etc.
        // we need to do this in column updater etc!

        return array(
            $this->dbTableName => array("ADD COLUMN `$this->columnName` $this->columnType")
        );
    }

    /**
     * Updates the action dimension in case the {@link $columnType} has changed. The update is already implemented based
     * on the {@link $columnName} and {@link $columnType}. This method is intended not to overwritten by plugin
     * developers as it is only supposed to make sure the column has the correct type. Adding additional custom "alter
     * table" actions would not really work since they would be executed with every {@link $columnType} change. So
     * adding an index here would be executed whenever the columnType changes resulting in an error if the index already
     * exists. If an index needs to be added after the first version is released a plugin update class should be
     * created since this makes sure it is only executed once.
     *
     * @return array An array containing the table name as key and an array of MySQL alter table statements that should
     *               be executed on the given table. Example:
     * ```
    array(
    'log_link_visit_action' => array("MODIFY COLUMN `$this->columnName` $this->columnType", "DROP COLUMN ...")
    );
    ```
     * @ignore
     */
    public function update()
    {
        if (empty($this->columnName) || empty($this->columnType) || empty($this->dbTableName)) {
            return array();
        }

        return array(
            $this->dbTableName => array("MODIFY COLUMN `$this->columnName` $this->columnType")
        );
    }

    /**
     * Uninstalls the dimension if a {@link $columnName} and {@link columnType} is set. In case you perform any custom
     * actions during {@link install()} - for instance adding an index - you should make sure to undo those actions by
     * overwriting this method. Make sure to call this parent method to make sure the uninstallation of the column
     * will be done.
     * @throws Exception
     * @api
     */
    public function uninstall()
    {
        if (empty($this->columnName) || empty($this->columnType) || empty($this->dbTableName)) {
            return;
        }

        try {
            $sql = "ALTER TABLE `" . Common::prefixTable($this->dbTableName) . "` DROP COLUMN `$this->columnName`";
            Db::exec($sql);
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1091')) {
                throw $e;
            }
        }
    }

    public function getCategory()
    {
        if (!empty($this->category)) {
            return Piwik::translate($this->category);
        }

        return $this->category;
    }

    public function getName()
    {
        if (!empty($this->nameSingular)) {
            return Piwik::translate($this->nameSingular);
        }

        return $this->nameSingular;
    }

    public function getNamePlural()
    {
        if (!empty($this->namePlural)) {
            return Piwik::translate($this->namePlural);
        }

        return $this->getName();
    }

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
        if ($this->segmentName && $this->category && $this->columnName && $this->dbTableName && $this->nameSingular) {
            $segment = new Segment();
            $segment->setSegment($this->segmentName);
            $segment->setCategory($this->category);
            $segment->setName($this->nameSingular);
            $segment->setSqlSegment($this->dbTableName . '.' . $this->columnName);

            $this->addSegment($segment);
        }
    }

    protected function configureMetrics()
    {
        if ($this->segmentName && $this->category && $this->columnName && $this->dbTableName && $this->nameSingular) {
            $metric = new ArchivedMetric($this, ArchivedMetric::AGGREGATION_COUNT);
            $this->addMetric($metric);

            $metric = new ArchivedMetric($this, ArchivedMetric::AGGREGATION_SUM);
            $this->addMetric($metric);
        }
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

    protected function addMetric(Metric $metric)
    {
        $this->metrics[] = $metric;
    }

    /**
     * Adds a new segment. It automatically sets the SQL segment depending on the column name in case none is set
     * already.
     * @see \Piwik\Columns\Dimension::addSegment()
     * @param Segment $segment
     * @api
     */
    protected function addSegment(Segment $segment)
    {
        if (!$segment->getType()) {
            $metricTypes = array(self::TYPE_NUMBER, self::TYPE_FLOAT, self::TYPE_MONEY, self::TYPE_DURATION_S, self::TYPE_DURATION_MS, self::TYPE_DATE, self::TYPE_DATETIME, self::TYPE_TIME);
            if (in_array($this->getType(), $metricTypes)) {
                $segment->setType(Segment::TYPE_METRIC);
            } else {
                $segment->setType(Segment::TYPE_DIMENSION);
            }
        }

        if (!$segment->getName() && $this->nameSingular) {
            $segment->setName($this->nameSingular);
        }

        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->columnName) && empty($sqlSegment)) {
            $segment->setSqlSegment($this->dbTableName . '.' . $this->columnName);
        }

        if ($this->acceptValues) {
            $segment->setAcceptedValues($this->acceptValues);
        }

        if ($this->sqlFilterValue) {
            $segment->setSqlFilterValue($this->sqlFilterValue);
        }

        if ($this->sqlFilter) {
            $segment->setSqlFilter($this->sqlFilter);
        }

        if (!$this->allowAnonymous) {
            $segment->setRequiresAtLeastViewAccess(true);
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

    public function getDbTableName()
    {
        return $this->dbTableName;
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
    public function getId()
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
        $cacheId = CacheId::pluginAware('AllDimensions');
        $cache   = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $plugins   = PluginManager::getInstance()->getPluginsLoadedAndActivated();
            $instances = array();

            /**
             * Triggered to add new dimensions that cannot be picked up automatically by the platform.
             * This is useful if the plugin allows a user to create reports / dimensions dynamically. For example
             * CustomDimensions or CustomVariables. There are a variable number of dimensions in this case and it
             * wouldn't be really possible to create a report file for one of these dimensions as it is not known
             * how many Custom Dimensions will exist.
             *
             * **Example**
             *
             *     public function addDimension(&$dimensions)
             *     {
             *         $dimensions[] = new MyCustomDimension();
             *     }
             *
             * @param Dimension[] $reports An array of dimensions
             */
            Piwik::postEvent('Dimension.addDimensions', array(&$instances));

            foreach ($plugins as $plugin) {
                foreach (self::getDimensions($plugin) as $instance) {
                    $instances[] = $instance;
                }
            }

            /**
             * Triggered to filter / restrict dimensions.
             *
             * **Example**
             *
             *     public function filterDimensions(&$dimensions)
             *     {
             *         foreach ($dimensions as $index => $dimension) {
             *              if ($dimension->getName() === 'Page URL') {}
             *                  unset($dimensions[$index]); // remove this dimension
             *              }
             *         }
             *     }
             *
             * @param Dimension[] $dimensions An array of dimensions
             */
            Piwik::postEvent('Dimension.filterDimensions', array(&$instances));

            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }

    public static function getDimensions(Plugin $plugin)
    {
        $columns = $plugin->findMultipleComponents('Columns', '\\Piwik\\Columns\\Dimension');
        $instances  = array();

        foreach ($columns as $colum) {
            $instances[] = new $colum();
        }

        return $instances;
    }

    /**
     * Creates a Dimension instance from a string ID (see {@link getId()}).
     *
     * @param string $dimensionId See {@link getId()}.
     * @return Dimension|null The created instance or null if there is no Dimension for
     *                        $dimensionId or if the plugin that contains the Dimension is
     *                        not loaded.
     * @api
     * @deprecated Please use DimensionProvider::factory instead
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

    /**
     * TODO in Piwik 4 rename to getColumnType, rename getColumnType to getDbColumnType
     *
     * @return string
     */
    public function getType()
    {
        if (!empty($this->type)) {
            return $this->type;
        }

        if (!empty($this->columnType)) {
            // best guess
            $type = strtolower($this->columnType);
            if (strpos($type, 'datetime') !== false) {
                return self::TYPE_DATETIME;
            } elseif (strpos($type, 'timestamp') !== false) {
                return self::TYPE_TIMESTAMP;
            } elseif (strpos($type, 'date') !== false) {
                return self::TYPE_DATETIME;
            } elseif (strpos($type, 'time') !== false) {
                return self::TYPE_TIME;
            } elseif (strpos($type, 'float') !== false) {
                return self::TYPE_FLOAT;
            } elseif (strpos($type, 'decimal') !== false) {
                return self::TYPE_FLOAT;
            } elseif (strpos($type, 'int') !== false) {
                return self::TYPE_NUMBER;
            }
        }

        return self::TYPE_TEXT;
    }

    /**
     * Get the list of configured segments.
     * @return Metric[]
     * @ignore
     */
    public function getMetrics()
    {
        if (empty($this->metrics)) {
            $this->configureMetrics();
        }

        return $this->metrics;
    }

    /**
     * Get the version of the dimension which is used for update checks.
     * @return string
     * @ignore
     */
    public function getVersion()
    {
        return $this->columnType;
    }

}
