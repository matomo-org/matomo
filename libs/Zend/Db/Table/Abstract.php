<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Select.php';

/**
 * @see Zend_Db
 */
require_once 'Zend/Db.php';

/**
 * Class for SQL table interface.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Db_Table_Abstract
{

    const ADAPTER          = 'db';
    const DEFINITION        = 'definition';
    const DEFINITION_CONFIG_NAME = 'definitionConfigName';
    const SCHEMA           = 'schema';
    const NAME             = 'name';
    const PRIMARY          = 'primary';
    const COLS             = 'cols';
    const METADATA         = 'metadata';
    const METADATA_CACHE   = 'metadataCache';
    const METADATA_CACHE_IN_CLASS = 'metadataCacheInClass';
    const ROW_CLASS        = 'rowClass';
    const ROWSET_CLASS     = 'rowsetClass';
    const REFERENCE_MAP    = 'referenceMap';
    const DEPENDENT_TABLES = 'dependentTables';
    const SEQUENCE         = 'sequence';

    const COLUMNS          = 'columns';
    const REF_TABLE_CLASS  = 'refTableClass';
    const REF_COLUMNS      = 'refColumns';
    const ON_DELETE        = 'onDelete';
    const ON_UPDATE        = 'onUpdate';

    const CASCADE          = 'cascade';
    const RESTRICT         = 'restrict';
    const SET_NULL         = 'setNull';

    const DEFAULT_NONE     = 'defaultNone';
    const DEFAULT_CLASS    = 'defaultClass';
    const DEFAULT_DB       = 'defaultDb';

    const SELECT_WITH_FROM_PART    = true;
    const SELECT_WITHOUT_FROM_PART = false;

    /**
     * Default Zend_Db_Adapter_Abstract object.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected static $_defaultDb;

    /**
     * Optional Zend_Db_Table_Definition object
     *
     * @var unknown_type
     */
    protected $_definition = null;

    /**
     * Optional definition config name used in concrete implementation
     *
     * @var string
     */
    protected $_definitionConfigName = null;

    /**
     * Default cache for information provided by the adapter's describeTable() method.
     *
     * @var Zend_Cache_Core
     */
    protected static $_defaultMetadataCache = null;

    /**
     * Zend_Db_Adapter_Abstract object.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * The schema name (default null means current schema)
     *
     * @var array
     */
    protected $_schema = null;

    /**
     * The table name.
     *
     * @var string
     */
    protected $_name = null;

    /**
     * The table column names derived from Zend_Db_Adapter_Abstract::describeTable().
     *
     * @var array
     */
    protected $_cols;

    /**
     * The primary key column or columns.
     * A compound key should be declared as an array.
     * You may declare a single-column primary key
     * as a string.
     *
     * @var mixed
     */
    protected $_primary = null;

    /**
     * If your primary key is a compound key, and one of the columns uses
     * an auto-increment or sequence-generated value, set _identity
     * to the ordinal index in the $_primary array for that column.
     * Note this index is the position of the column in the primary key,
     * not the position of the column in the table.  The primary key
     * array is 1-based.
     *
     * @var integer
     */
    protected $_identity = 1;

    /**
     * Define the logic for new values in the primary key.
     * May be a string, boolean true, or boolean false.
     *
     * @var mixed
     */
    protected $_sequence = true;

    /**
     * Information provided by the adapter's describeTable() method.
     *
     * @var array
     */
    protected $_metadata = array();

    /**
     * Cache for information provided by the adapter's describeTable() method.
     *
     * @var Zend_Cache_Core
     */
    protected $_metadataCache = null;

    /**
     * Flag: whether or not to cache metadata in the class
     * @var bool
     */
    protected $_metadataCacheInClass = true;

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Zend_Db_Table_Row';

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'Zend_Db_Table_Rowset';

    /**
     * Associative array map of declarative referential integrity rules.
     * This array has one entry per foreign key in the current table.
     * Each key is a mnemonic name for one reference rule.
     *
     * Each value is also an associative array, with the following keys:
     * - columns       = array of names of column(s) in the child table.
     * - refTableClass = class name of the parent table.
     * - refColumns    = array of names of column(s) in the parent table,
     *                   in the same order as those in the 'columns' entry.
     * - onDelete      = "cascade" means that a delete in the parent table also
     *                   causes a delete of referencing rows in the child table.
     * - onUpdate      = "cascade" means that an update of primary key values in
     *                   the parent table also causes an update of referencing
     *                   rows in the child table.
     *
     * @var array
     */
    protected $_referenceMap = array();

    /**
     * Simple array of class names of tables that are "children" of the current
     * table, in other words tables that contain a foreign key to this one.
     * Array elements are not table names; they are class names of classes that
     * extend Zend_Db_Table_Abstract.
     *
     * @var array
     */
    protected $_dependentTables = array();


    protected $_defaultSource = self::DEFAULT_NONE;
    protected $_defaultValues = array();

    /**
     * Constructor.
     *
     * Supported params for $config are:
     * - db              = user-supplied instance of database connector,
     *                     or key name of registry instance.
     * - name            = table name.
     * - primary         = string or array of primary key(s).
     * - rowClass        = row class name.
     * - rowsetClass     = rowset class name.
     * - referenceMap    = array structure to declare relationship
     *                     to parent tables.
     * - dependentTables = array of child tables.
     * - metadataCache   = cache for information from adapter describeTable().
     *
     * @param  mixed $config Array of user-specified config options, or just the Db Adapter.
     * @return void
     */
    public function __construct($config = array())
    {
        /**
         * Allow a scalar argument to be the Adapter object or Registry key.
         */
        if (!is_array($config)) {
            $config = array(self::ADAPTER => $config);
        }

        if ($config) {
            $this->setOptions($config);
        }

        $this->_setup();
        $this->init();
    }

    /**
     * setOptions()
     *
     * @param array $options
     * @return Zend_Db_Table_Abstract
     */
    public function setOptions(Array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case self::ADAPTER:
                    $this->_setAdapter($value);
                    break;
                case self::DEFINITION:
                    $this->setDefinition($value);
                    break;
                case self::DEFINITION_CONFIG_NAME:
                    $this->setDefinitionConfigName($value);
                    break;
                case self::SCHEMA:
                    $this->_schema = (string) $value;
                    break;
                case self::NAME:
                    $this->_name = (string) $value;
                    break;
                case self::PRIMARY:
                    $this->_primary = (array) $value;
                    break;
                case self::ROW_CLASS:
                    $this->setRowClass($value);
                    break;
                case self::ROWSET_CLASS:
                    $this->setRowsetClass($value);
                    break;
                case self::REFERENCE_MAP:
                    $this->setReferences($value);
                    break;
                case self::DEPENDENT_TABLES:
                    $this->setDependentTables($value);
                    break;
                case self::METADATA_CACHE:
                    $this->_setMetadataCache($value);
                    break;
                case self::METADATA_CACHE_IN_CLASS:
                    $this->setMetadataCacheInClass($value);
                    break;
                case self::SEQUENCE:
                    $this->_setSequence($value);
                    break;
                default:
                    // ignore unrecognized configuration directive
                    break;
            }
        }

        return $this;
    }

    /**
     * setDefinition()
     *
     * @param Zend_Db_Table_Definition $definition
     * @return Zend_Db_Table_Abstract
     */
    public function setDefinition(Zend_Db_Table_Definition $definition)
    {
        $this->_definition = $definition;
        return $this;
    }

    /**
     * getDefinition()
     *
     * @return Zend_Db_Table_Definition|null
     */
    public function getDefinition()
    {
        return $this->_definition;
    }

    /**
     * setDefinitionConfigName()
     *
     * @param string $definition
     * @return Zend_Db_Table_Abstract
     */
    public function setDefinitionConfigName($definitionConfigName)
    {
        $this->_definitionConfigName = $definitionConfigName;
        return $this;
    }

    /**
     * getDefinitionConfigName()
     *
     * @return string
     */
    public function getDefinitionConfigName()
    {
        return $this->_definitionConfigName;
    }

    /**
     * @param  string $classname
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    public function setRowClass($classname)
    {
        $this->_rowClass = (string) $classname;

        return $this;
    }

    /**
     * @return string
     */
    public function getRowClass()
    {
        return $this->_rowClass;
    }

    /**
     * @param  string $classname
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    public function setRowsetClass($classname)
    {
        $this->_rowsetClass = (string) $classname;

        return $this;
    }

    /**
     * @return string
     */
    public function getRowsetClass()
    {
        return $this->_rowsetClass;
    }

    /**
     * Add a reference to the reference map
     *
     * @param string $ruleKey
     * @param string|array $columns
     * @param string $refTableClass
     * @param string|array $refColumns
     * @param string $onDelete
     * @param string $onUpdate
     * @return Zend_Db_Table_Abstract
     */
    public function addReference($ruleKey, $columns, $refTableClass, $refColumns,
                                 $onDelete = null, $onUpdate = null)
    {
        $reference = array(self::COLUMNS         => (array) $columns,
                           self::REF_TABLE_CLASS => $refTableClass,
                           self::REF_COLUMNS     => (array) $refColumns);

        if (!empty($onDelete)) {
            $reference[self::ON_DELETE] = $onDelete;
        }

        if (!empty($onUpdate)) {
            $reference[self::ON_UPDATE] = $onUpdate;
        }

        $this->_referenceMap[$ruleKey] = $reference;

        return $this;
    }

    /**
     * @param array $referenceMap
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    public function setReferences(array $referenceMap)
    {
        $this->_referenceMap = $referenceMap;

        return $this;
    }

    /**
     * @param string $tableClassname
     * @param string $ruleKey OPTIONAL
     * @return array
     * @throws Zend_Db_Table_Exception
     */
    public function getReference($tableClassname, $ruleKey = null)
    {
        $thisClass = get_class($this);
        if ($thisClass === 'Zend_Db_Table') {
            $thisClass = $this->_definitionConfigName;
        }
        $refMap = $this->_getReferenceMapNormalized();
        if ($ruleKey !== null) {
            if (!isset($refMap[$ruleKey])) {
                require_once "Zend/Db/Table/Exception.php";
                throw new Zend_Db_Table_Exception("No reference rule \"$ruleKey\" from table $thisClass to table $tableClassname");
            }
            if ($refMap[$ruleKey][self::REF_TABLE_CLASS] != $tableClassname) {
                require_once "Zend/Db/Table/Exception.php";
                throw new Zend_Db_Table_Exception("Reference rule \"$ruleKey\" does not reference table $tableClassname");
            }
            return $refMap[$ruleKey];
        }
        foreach ($refMap as $reference) {
            if ($reference[self::REF_TABLE_CLASS] == $tableClassname) {
                return $reference;
            }
        }
        require_once "Zend/Db/Table/Exception.php";
        throw new Zend_Db_Table_Exception("No reference from table $thisClass to table $tableClassname");
    }

    /**
     * @param  array $dependentTables
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    public function setDependentTables(array $dependentTables)
    {
        $this->_dependentTables = $dependentTables;

        return $this;
    }

    /**
     * @return array
     */
    public function getDependentTables()
    {
        return $this->_dependentTables;
    }

    /**
     * set the defaultSource property - this tells the table class where to find default values
     *
     * @param string $defaultSource
     * @return Zend_Db_Table_Abstract
     */
    public function setDefaultSource($defaultSource = self::DEFAULT_NONE)
    {
        if (!in_array($defaultSource, array(self::DEFAULT_CLASS, self::DEFAULT_DB, self::DEFAULT_NONE))) {
            $defaultSource = self::DEFAULT_NONE;
        }

        $this->_defaultSource = $defaultSource;
        return $this;
    }

    /**
     * returns the default source flag that determines where defaultSources come from
     *
     * @return unknown
     */
    public function getDefaultSource()
    {
        return $this->_defaultSource;
    }

    /**
     * set the default values for the table class
     *
     * @param array $defaultValues
     * @return Zend_Db_Table_Abstract
     */
    public function setDefaultValues(Array $defaultValues)
    {
        foreach ($defaultValues as $defaultName => $defaultValue) {
            if (array_key_exists($defaultName, $this->_metadata)) {
                $this->_defaultValues[$defaultName] = $defaultValue;
            }
        }
        return $this;
    }

    public function getDefaultValues()
    {
        return $this->_defaultValues;
    }


    /**
     * Sets the default Zend_Db_Adapter_Abstract for all Zend_Db_Table objects.
     *
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return void
     */
    public static function setDefaultAdapter($db = null)
    {
        self::$_defaultDb = self::_setupAdapter($db);
    }

    /**
     * Gets the default Zend_Db_Adapter_Abstract for all Zend_Db_Table objects.
     *
     * @return Zend_Db_Adapter_Abstract or null
     */
    public static function getDefaultAdapter()
    {
        return self::$_defaultDb;
    }

    /**
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    protected function _setAdapter($db)
    {
        $this->_db = self::_setupAdapter($db);
        return $this;
    }

    /**
     * Gets the Zend_Db_Adapter_Abstract for this particular Zend_Db_Table object.
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_db;
    }

    /**
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Table_Exception
     */
    protected static function _setupAdapter($db)
    {
        if ($db === null) {
            return null;
        }
        if (is_string($db)) {
            require_once 'Zend/Registry.php';
            $db = Zend_Registry::get($db);
        }
        if (!$db instanceof Zend_Db_Adapter_Abstract) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('Argument must be of type Zend_Db_Adapter_Abstract, or a Registry key where a Zend_Db_Adapter_Abstract object is stored');
        }
        return $db;
    }

    /**
     * Sets the default metadata cache for information returned by Zend_Db_Adapter_Abstract::describeTable().
     *
     * If $defaultMetadataCache is null, then no metadata cache is used by default.
     *
     * @param  mixed $metadataCache Either a Cache object, or a string naming a Registry key
     * @return void
     */
    public static function setDefaultMetadataCache($metadataCache = null)
    {
        self::$_defaultMetadataCache = self::_setupMetadataCache($metadataCache);
    }

    /**
     * Gets the default metadata cache for information returned by Zend_Db_Adapter_Abstract::describeTable().
     *
     * @return Zend_Cache_Core or null
     */
    public static function getDefaultMetadataCache()
    {
        return self::$_defaultMetadataCache;
    }

    /**
     * Sets the metadata cache for information returned by Zend_Db_Adapter_Abstract::describeTable().
     *
     * If $metadataCache is null, then no metadata cache is used. Since there is no opportunity to reload metadata
     * after instantiation, this method need not be public, particularly because that it would have no effect
     * results in unnecessary API complexity. To configure the metadata cache, use the metadataCache configuration
     * option for the class constructor upon instantiation.
     *
     * @param  mixed $metadataCache Either a Cache object, or a string naming a Registry key
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    protected function _setMetadataCache($metadataCache)
    {
        $this->_metadataCache = self::_setupMetadataCache($metadataCache);
        return $this;
    }

    /**
     * Gets the metadata cache for information returned by Zend_Db_Adapter_Abstract::describeTable().
     *
     * @return Zend_Cache_Core or null
     */
    public function getMetadataCache()
    {
        return $this->_metadataCache;
    }

    /**
     * Indicate whether metadata should be cached in the class for the duration
     * of the instance
     *
     * @param  bool $flag
     * @return Zend_Db_Table_Abstract
     */
    public function setMetadataCacheInClass($flag)
    {
        $this->_metadataCacheInClass = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve flag indicating if metadata should be cached for duration of
     * instance
     *
     * @return bool
     */
    public function metadataCacheInClass()
    {
        return $this->_metadataCacheInClass;
    }

    /**
     * @param mixed $metadataCache Either a Cache object, or a string naming a Registry key
     * @return Zend_Cache_Core
     * @throws Zend_Db_Table_Exception
     */
    protected static function _setupMetadataCache($metadataCache)
    {
        if ($metadataCache === null) {
            return null;
        }
        if (is_string($metadataCache)) {
            require_once 'Zend/Registry.php';
            $metadataCache = Zend_Registry::get($metadataCache);
        }
        if (!$metadataCache instanceof Zend_Cache_Core) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('Argument must be of type Zend_Cache_Core, or a Registry key where a Zend_Cache_Core object is stored');
        }
        return $metadataCache;
    }

    /**
     * Sets the sequence member, which defines the behavior for generating
     * primary key values in new rows.
     * - If this is a string, then the string names the sequence object.
     * - If this is boolean true, then the key uses an auto-incrementing
     *   or identity mechanism.
     * - If this is boolean false, then the key is user-defined.
     *   Use this for natural keys, for example.
     *
     * @param mixed $sequence
     * @return Zend_Db_Table_Adapter_Abstract Provides a fluent interface
     */
    protected function _setSequence($sequence)
    {
        $this->_sequence = $sequence;

        return $this;
    }

    /**
     * Turnkey for initialization of a table object.
     * Calls other protected methods for individual tasks, to make it easier
     * for a subclass to override part of the setup logic.
     *
     * @return void
     */
    protected function _setup()
    {
        $this->_setupDatabaseAdapter();
        $this->_setupTableName();
    }

    /**
     * Initialize database adapter.
     *
     * @return void
     */
    protected function _setupDatabaseAdapter()
    {
        if (! $this->_db) {
            $this->_db = self::getDefaultAdapter();
            if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception('No adapter found for ' . get_class($this));
            }
        }
    }

    /**
     * Initialize table and schema names.
     *
     * If the table name is not set in the class definition,
     * use the class name itself as the table name.
     *
     * A schema name provided with the table name (e.g., "schema.table") overrides
     * any existing value for $this->_schema.
     *
     * @return void
     */
    protected function _setupTableName()
    {
        if (! $this->_name) {
            $this->_name = get_class($this);
        } else if (strpos($this->_name, '.')) {
            list($this->_schema, $this->_name) = explode('.', $this->_name);
        }
    }

    /**
     * Initializes metadata.
     *
     * If metadata cannot be loaded from cache, adapter's describeTable() method is called to discover metadata
     * information. Returns true if and only if the metadata are loaded from cache.
     *
     * @return boolean
     * @throws Zend_Db_Table_Exception
     */
    protected function _setupMetadata()
    {
        if ($this->metadataCacheInClass() && (count($this->_metadata) > 0)) {
            return true;
        }

        // Assume that metadata will be loaded from cache
        $isMetadataFromCache = true;

        // If $this has no metadata cache but the class has a default metadata cache
        if (null === $this->_metadataCache && null !== self::$_defaultMetadataCache) {
            // Make $this use the default metadata cache of the class
            $this->_setMetadataCache(self::$_defaultMetadataCache);
        }

        // If $this has a metadata cache
        if (null !== $this->_metadataCache) {
            // Define the cache identifier where the metadata are saved

            //get db configuration
            $dbConfig = $this->_db->getConfig();

            // Define the cache identifier where the metadata are saved
            $cacheId = md5( // port:host/dbname:schema.table (based on availabilty)
                (isset($dbConfig['options']['port']) ? ':'.$dbConfig['options']['port'] : null)
                . (isset($dbConfig['options']['host']) ? ':'.$dbConfig['options']['host'] : null)
                . '/'.$dbConfig['dbname'].':'.$this->_schema.'.'.$this->_name
                );
        }

        // If $this has no metadata cache or metadata cache misses
        if (null === $this->_metadataCache || !($metadata = $this->_metadataCache->load($cacheId))) {
            // Metadata are not loaded from cache
            $isMetadataFromCache = false;
            // Fetch metadata from the adapter's describeTable() method
            $metadata = $this->_db->describeTable($this->_name, $this->_schema);
            // If $this has a metadata cache, then cache the metadata
            if (null !== $this->_metadataCache && !$this->_metadataCache->save($metadata, $cacheId)) {
                /**
                 * @see Zend_Db_Table_Exception
                 */
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception('Failed saving metadata to metadataCache');
            }
        }

        // Assign the metadata to $this
        $this->_metadata = $metadata;

        // Return whether the metadata were loaded from cache
        return $isMetadataFromCache;
    }

    /**
     * Retrieve table columns
     *
     * @return array
     */
    protected function _getCols()
    {
        if (null === $this->_cols) {
            $this->_setupMetadata();
            $this->_cols = array_keys($this->_metadata);
        }
        return $this->_cols;
    }

    /**
     * Initialize primary key from metadata.
     * If $_primary is not defined, discover primary keys
     * from the information returned by describeTable().
     *
     * @return void
     * @throws Zend_Db_Table_Exception
     */
    protected function _setupPrimaryKey()
    {
        if (!$this->_primary) {
            $this->_setupMetadata();
            $this->_primary = array();
            foreach ($this->_metadata as $col) {
                if ($col['PRIMARY']) {
                    $this->_primary[ $col['PRIMARY_POSITION'] ] = $col['COLUMN_NAME'];
                    if ($col['IDENTITY']) {
                        $this->_identity = $col['PRIMARY_POSITION'];
                    }
                }
            }
            // if no primary key was specified and none was found in the metadata
            // then throw an exception.
            if (empty($this->_primary)) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception('A table must have a primary key, but none was found');
            }
        } else if (!is_array($this->_primary)) {
            $this->_primary = array(1 => $this->_primary);
        } else if (isset($this->_primary[0])) {
            array_unshift($this->_primary, null);
            unset($this->_primary[0]);
        }

        $cols = $this->_getCols();
        if (! array_intersect((array) $this->_primary, $cols) == (array) $this->_primary) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Primary key column(s) ("
                . implode(',', (array) $this->_primary)
                . ") are not columns in this table ("
                . implode(',', $cols)
                . ")");
        }

        $primary    = (array) $this->_primary;
        $pkIdentity = $primary[(int) $this->_identity];

        /**
         * Special case for PostgreSQL: a SERIAL key implicitly uses a sequence
         * object whose name is "<table>_<column>_seq".
         */
        if ($this->_sequence === true && $this->_db instanceof Zend_Db_Adapter_Pdo_Pgsql) {
            $this->_sequence = $this->_db->quoteIdentifier("{$this->_name}_{$pkIdentity}_seq");
            if ($this->_schema) {
                $this->_sequence = $this->_db->quoteIdentifier($this->_schema) . '.' . $this->_sequence;
            }
        }
    }

    /**
     * Returns a normalized version of the reference map
     *
     * @return array
     */
    protected function _getReferenceMapNormalized()
    {
        $referenceMapNormalized = array();

        foreach ($this->_referenceMap as $rule => $map) {

            $referenceMapNormalized[$rule] = array();

            foreach ($map as $key => $value) {
                switch ($key) {

                    // normalize COLUMNS and REF_COLUMNS to arrays
                    case self::COLUMNS:
                    case self::REF_COLUMNS:
                        if (!is_array($value)) {
                            $referenceMapNormalized[$rule][$key] = array($value);
                        } else {
                            $referenceMapNormalized[$rule][$key] = $value;
                        }
                        break;

                    // other values are copied as-is
                    default:
                        $referenceMapNormalized[$rule][$key] = $value;
                        break;
                }
            }
        }

        return $referenceMapNormalized;
    }

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Returns table information.
     *
     * You can elect to return only a part of this information by supplying its key name,
     * otherwise all information is returned as an array.
     *
     * @param  $key The specific info part to return OPTIONAL
     * @return mixed
     */
    public function info($key = null)
    {
        $this->_setupPrimaryKey();

        $info = array(
            self::SCHEMA           => $this->_schema,
            self::NAME             => $this->_name,
            self::COLS             => $this->_getCols(),
            self::PRIMARY          => (array) $this->_primary,
            self::METADATA         => $this->_metadata,
            self::ROW_CLASS        => $this->getRowClass(),
            self::ROWSET_CLASS     => $this->getRowsetClass(),
            self::REFERENCE_MAP    => $this->_referenceMap,
            self::DEPENDENT_TABLES => $this->_dependentTables,
            self::SEQUENCE         => $this->_sequence
        );

        if ($key === null) {
            return $info;
        }

        if (!array_key_exists($key, $info)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('There is no table information for the key "' . $key . '"');
        }

        return $info[$key];
    }

    /**
     * Returns an instance of a Zend_Db_Table_Select object.
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Zend_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        require_once 'Zend/Db/Table/Select.php';
        $select = new Zend_Db_Table_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }
        return $select;
    }

    /**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data)
    {
        $this->_setupPrimaryKey();

        /**
         * Zend_Db_Table assumes that if you have a compound primary key
         * and one of the columns in the key uses a sequence,
         * it's the _first_ column in the compound key.
         */
        $primary = (array) $this->_primary;
        $pkIdentity = $primary[(int)$this->_identity];

        /**
         * If this table uses a database sequence object and the data does not
         * specify a value, then get the next ID from the sequence and add it
         * to the row.  We assume that only the first column in a compound
         * primary key takes a value from a sequence.
         */
        if (is_string($this->_sequence) && !isset($data[$pkIdentity])) {
            $data[$pkIdentity] = $this->_db->nextSequenceId($this->_sequence);
        }

        /**
         * If the primary key can be generated automatically, and no value was
         * specified in the user-supplied data, then omit it from the tuple.
         */
        if (array_key_exists($pkIdentity, $data) && $data[$pkIdentity] === null) {
            unset($data[$pkIdentity]);
        }

        /**
         * INSERT the new row.
         */
        $tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
        $this->_db->insert($tableSpec, $data);

        /**
         * Fetch the most recent ID generated by an auto-increment
         * or IDENTITY column, unless the user has specified a value,
         * overriding the auto-increment mechanism.
         */
        if ($this->_sequence === true && !isset($data[$pkIdentity])) {
            $data[$pkIdentity] = $this->_db->lastInsertId();
        }

        /**
         * Return the primary key value if the PK is a single column,
         * else return an associative array of the PK column/value pairs.
         */
        $pkData = array_intersect_key($data, array_flip($primary));
        if (count($primary) == 1) {
            reset($pkData);
            return current($pkData);
        }

        return $pkData;
    }

    /**
     * Check if the provided column is an identity of the table
     *
     * @param  string $column
     * @throws Zend_Db_Table_Exception
     * @return boolean
     */
    public function isIdentity($column)
    {
        $this->_setupPrimaryKey();

        if (!isset($this->_metadata[$column])) {
            /**
             * @see Zend_Db_Table_Exception
             */
            require_once 'Zend/Db/Table/Exception.php';

            throw new Zend_Db_Table_Exception('Column "' . $column . '" not found in table.');
        }

        return (bool) $this->_metadata[$column]['IDENTITY'];
    }

    /**
     * Updates existing rows.
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where)
    {
        $tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
        return $this->_db->update($tableSpec, $data, $where);
    }

    /**
     * Called by a row object for the parent table's class during save() method.
     *
     * @param  string $parentTableClassname
     * @param  array  $oldPrimaryKey
     * @param  array  $newPrimaryKey
     * @return int
     */
    public function _cascadeUpdate($parentTableClassname, array $oldPrimaryKey, array $newPrimaryKey)
    {
        $this->_setupMetadata();
        $rowsAffected = 0;
        foreach ($this->_getReferenceMapNormalized() as $map) {
            if ($map[self::REF_TABLE_CLASS] == $parentTableClassname && isset($map[self::ON_UPDATE])) {
                switch ($map[self::ON_UPDATE]) {
                    case self::CASCADE:
                        $newRefs = array();
                        $where = array();
                        for ($i = 0; $i < count($map[self::COLUMNS]); ++$i) {
                            $col = $this->_db->foldCase($map[self::COLUMNS][$i]);
                            $refCol = $this->_db->foldCase($map[self::REF_COLUMNS][$i]);
                            if (array_key_exists($refCol, $newPrimaryKey)) {
                                $newRefs[$col] = $newPrimaryKey[$refCol];
                            }
                            $type = $this->_metadata[$col]['DATA_TYPE'];
                            $where[] = $this->_db->quoteInto(
                                $this->_db->quoteIdentifier($col, true) . ' = ?',
                                $oldPrimaryKey[$refCol], $type);
                        }
                        $rowsAffected += $this->update($newRefs, $where);
                        break;
                    default:
                        // no action
                        break;
                }
            }
        }
        return $rowsAffected;
    }

    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        $tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
        return $this->_db->delete($tableSpec, $where);
    }

    /**
     * Called by parent table's class during delete() method.
     *
     * @param  string $parentTableClassname
     * @param  array  $primaryKey
     * @return int    Number of affected rows
     */
    public function _cascadeDelete($parentTableClassname, array $primaryKey)
    {
        $this->_setupMetadata();
        $rowsAffected = 0;
        foreach ($this->_getReferenceMapNormalized() as $map) {
            if ($map[self::REF_TABLE_CLASS] == $parentTableClassname && isset($map[self::ON_DELETE])) {
                switch ($map[self::ON_DELETE]) {
                    case self::CASCADE:
                        $where = array();
                        for ($i = 0; $i < count($map[self::COLUMNS]); ++$i) {
                            $col = $this->_db->foldCase($map[self::COLUMNS][$i]);
                            $refCol = $this->_db->foldCase($map[self::REF_COLUMNS][$i]);
                            $type = $this->_metadata[$col]['DATA_TYPE'];
                            $where[] = $this->_db->quoteInto(
                                $this->_db->quoteIdentifier($col, true) . ' = ?',
                                $primaryKey[$refCol], $type);
                        }
                        $rowsAffected += $this->delete($where);
                        break;
                    default:
                        // no action
                        break;
                }
            }
        }
        return $rowsAffected;
    }

    /**
     * Fetches rows by primary key.  The argument specifies one or more primary
     * key value(s).  To find multiple rows by primary key, the argument must
     * be an array.
     *
     * This method accepts a variable number of arguments.  If the table has a
     * multi-column primary key, the number of arguments must be the same as
     * the number of columns in the primary key.  To find multiple rows in a
     * table with a multi-column primary key, each argument must be an array
     * with the same number of elements.
     *
     * The find() method always returns a Rowset object, even if only one row
     * was found.
     *
     * @param  mixed $key The value(s) of the primary keys.
     * @return Zend_Db_Table_Rowset_Abstract Row(s) matching the criteria.
     * @throws Zend_Db_Table_Exception
     */
    public function find()
    {
        $this->_setupPrimaryKey();
        $args = func_get_args();
        $keyNames = array_values((array) $this->_primary);

        if (count($args) < count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too few columns for the primary key");
        }

        if (count($args) > count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too many columns for the primary key");
        }

        $whereList = array();
        $numberTerms = 0;
        foreach ($args as $keyPosition => $keyValues) {
            $keyValuesCount = count($keyValues);
            // Coerce the values to an array.
            // Don't simply typecast to array, because the values
            // might be Zend_Db_Expr objects.
            if (!is_array($keyValues)) {
                $keyValues = array($keyValues);
            }
            if ($numberTerms == 0) {
                $numberTerms = $keyValuesCount;
            } else if ($keyValuesCount != $numberTerms) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception("Missing value(s) for the primary key");
            }
            $keyValues = array_values($keyValues);
            for ($i = 0; $i < $keyValuesCount; ++$i) {
                if (!isset($whereList[$i])) {
                    $whereList[$i] = array();
                }
                $whereList[$i][$keyPosition] = $keyValues[$i];
            }
        }

        $whereClause = null;
        if (count($whereList)) {
            $whereOrTerms = array();
            $tableName = $this->_db->quoteTableAs($this->_name, null, true);
            foreach ($whereList as $keyValueSets) {
                $whereAndTerms = array();
                foreach ($keyValueSets as $keyPosition => $keyValue) {
                    $type = $this->_metadata[$keyNames[$keyPosition]]['DATA_TYPE'];
                    $columnName = $this->_db->quoteIdentifier($keyNames[$keyPosition], true);
                    $whereAndTerms[] = $this->_db->quoteInto(
                        $tableName . '.' . $columnName . ' = ?',
                        $keyValue, $type);
                }
                $whereOrTerms[] = '(' . implode(' AND ', $whereAndTerms) . ')';
            }
            $whereClause = '(' . implode(' OR ', $whereOrTerms) . ')';
        }

        // issue ZF-5775 (empty where clause should return empty rowset)
        if ($whereClause == null) {
            $rowsetClass = $this->getRowsetClass();
            if (!class_exists($rowsetClass)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($rowsetClass);
            }
            return new $rowsetClass(array('table' => $this, 'rowClass' => $this->getRowClass(), 'stored' => true));
        }

        return $this->fetchAll($whereClause);
    }

    /**
     * Fetches all rows.
     *
     * Honors the Zend_Db_Adapter fetch mode.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            if ($count !== null || $offset !== null) {
                $select->limit($count, $offset);
            }

        } else {
            $select = $where;
        }

        $rows = $this->_fetch($select);

        $data  = array(
            'table'    => $this,
            'data'     => $rows,
            'readOnly' => $select->isReadOnly(),
            'rowClass' => $this->getRowClass(),
            'stored'   => true
        );

        $rowsetClass = $this->getRowsetClass();
        if (!class_exists($rowsetClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowsetClass);
        }
        return new $rowsetClass($data);
    }

    /**
     * Fetches one row in an object of type Zend_Db_Table_Row_Abstract,
     * or returns null if no row matches the specified criteria.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function fetchRow($where = null, $order = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            $select->limit(1);

        } else {
            $select = $where->limit(1);
        }

        $rows = $this->_fetch($select);

        if (count($rows) == 0) {
            return null;
        }

        $data = array(
            'table'   => $this,
            'data'     => $rows[0],
            'readOnly' => $select->isReadOnly(),
            'stored'  => true
        );

        $rowClass = $this->getRowClass();
        if (!class_exists($rowClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowClass);
        }
        return new $rowClass($data);
    }

    /**
     * Fetches a new blank row (not from the database).
     *
     * @return Zend_Db_Table_Row_Abstract
     * @deprecated since 0.9.3 - use createRow() instead.
     */
    public function fetchNew()
    {
        return $this->createRow();
    }

    /**
     * Fetches a new blank row (not from the database).
     *
     * @param  array $data OPTIONAL data to populate in the new row.
     * @param  string $defaultSource OPTIONAL flag to force default values into new row
     * @return Zend_Db_Table_Row_Abstract
     */
    public function createRow(array $data = array(), $defaultSource = null)
    {
        $cols     = $this->_getCols();
        $defaults = array_combine($cols, array_fill(0, count($cols), null));

        // nothing provided at call-time, take the class value
        if ($defaultSource == null) {
            $defaultSource = $this->_defaultSource;
        }

        if (!in_array($defaultSource, array(self::DEFAULT_CLASS, self::DEFAULT_DB, self::DEFAULT_NONE))) {
            $defaultSource = self::DEFAULT_NONE;
        }

        if ($defaultSource == self::DEFAULT_DB) {
            foreach ($this->_metadata as $metadataName => $metadata) {
                if (($metadata['DEFAULT'] != null) &&
                    ($metadata['NULLABLE'] !== true || ($metadata['NULLABLE'] === true && isset($this->_defaultValues[$metadataName]) && $this->_defaultValues[$metadataName] === true)) &&
                    (!(isset($this->_defaultValues[$metadataName]) && $this->_defaultValues[$metadataName] === false))) {
                    $defaults[$metadataName] = $metadata['DEFAULT'];
                }
            }
        } elseif ($defaultSource == self::DEFAULT_CLASS && $this->_defaultValues) {
            foreach ($this->_defaultValues as $defaultName => $defaultValue) {
                if (array_key_exists($defaultName, $defaults)) {
                    $defaults[$defaultName] = $defaultValue;
                }
            }
        }

        $config = array(
            'table'    => $this,
            'data'     => $defaults,
            'readOnly' => false,
            'stored'   => false
        );

        $rowClass = $this->getRowClass();
        if (!class_exists($rowClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowClass);
        }
        $row = new $rowClass($config);
        $row->setFromArray($data);
        return $row;
    }

    /**
     * Generate WHERE clause from user-supplied string or array
     *
     * @param  string|array $where  OPTIONAL An SQL WHERE clause.
     * @return Zend_Db_Table_Select
     */
    protected function _where(Zend_Db_Table_Select $select, $where)
    {
        $where = (array) $where;

        foreach ($where as $key => $val) {
            // is $key an int?
            if (is_int($key)) {
                // $val is the full condition
                $select->where($val);
            } else {
                // $key is the condition with placeholder,
                // and $val is quoted into the condition
                $select->where($key, $val);
            }
        }

        return $select;
    }

    /**
     * Generate ORDER clause from user-supplied string or array
     *
     * @param  string|array $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Select
     */
    protected function _order(Zend_Db_Table_Select $select, $order)
    {
        if (!is_array($order)) {
            $order = array($order);
        }

        foreach ($order as $val) {
            $select->order($val);
        }

        return $select;
    }

    /**
     * Support method for fetching rows.
     *
     * @param  Zend_Db_Table_Select $select  query options.
     * @return array An array containing the row results in FETCH_ASSOC mode.
     */
    protected function _fetch(Zend_Db_Table_Select $select)
    {
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        return $data;
    }

}
