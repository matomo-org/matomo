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
use Piwik\CacheId;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ComponentFactory;
use Piwik\Plugin\Segment;
use Piwik\Cache as PiwikCache;
use Piwik\Plugin\Manager as PluginManager;

/**
 * TODO to be removed in Piwik 4 and code copied into column class
 * @deprecated  Please use `Piwik\Columns\Column` instead.
 * @api
 * @since 2.5.0
 */
abstract class Dimension
{
    const COMPONENT_SUBNAMESPACE = 'Columns';

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

    protected $dbTableName = '';

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
     * @return Column[]
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
             * @param Column[] $reports An array of dimensions
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
             * @param Column[] $dimensions An array of dimensions
             */
            Piwik::postEvent('Dimension.filterDimensions', array(&$instances));

            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }

    public static function getDimensions(Plugin $plugin)
    {
        $columns = $plugin->findMultipleComponents('Columns', '\\Piwik\\Columns\\Column');
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
     * @return Column|null The created instance or null if there is no Dimension for
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
