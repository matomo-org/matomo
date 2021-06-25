<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Container\StaticContainer;

/**
 * Convenient key-value storage for user specified options and temporary
 * data that needs to be persisted beyond one request.
 *
 * ### Examples
 *
 * **Setting and getting options**
 *
 *     $optionValue = Option::get('MyPlugin.MyOptionName');
 *     if ($optionValue === false) {
 *         // if not set, set it
 *         Option::set('MyPlugin.MyOptionName', 'my option value');
 *     }
 *
 * **Storing user specific options**
 *
 *     $userName = // ...
 *     Option::set('MyPlugin.MyOptionName.' . $userName, 'my option value');
 *
 * **Clearing user specific options**
 *
 *     Option::deleteLike('MyPlugin.MyOptionName.%');
 *
 * @api
 */
class Option
{
    /**
     * Returns the option value for the requested option `$name`.
     *
     * @param string $name The option name.
     * @return string|false The value or `false`, if not found.
     */
    public static function get($name)
    {
        return self::getInstance()->getValue($name);
    }

    /**
     * Returns option values for options whose names are like a given pattern. Only `%` is supported as part of the
     * pattern.
     *
     * @param string $namePattern The pattern used in the SQL `LIKE` expression
     *                            used to SELECT options.`'%'` characters should be used as wildcard. Underscore match is not supported.
     * @return array Array mapping option names with option values.
     */
    public static function getLike($namePattern)
    {
        return self::getInstance()->getNameLike($namePattern);
    }

    /**
     * Sets an option value by name.
     *
     * @param string $name The option name.
     * @param string $value The value to set the option to.
     * @param int $autoLoad If set to 1, this option value will be automatically loaded when Piwik is initialized;
     *                      should be set to 1 for options that will be used in every Piwik request.
     */
    public static function set($name, $value, $autoload = 0)
    {
        self::getInstance()->setValue($name, $value, $autoload);
    }

    /**
     * Deletes an option.
     *
     * @param string $name Option name to match exactly.
     * @param string $value If supplied the option will be deleted only if its value matches this value.
     */
    public static function delete($name, $value = null)
    {
        self::getInstance()->deleteValue($name, $value);
    }

    /**
     * Deletes all options that match the supplied pattern. Only `%` is supported as part of the
     * pattern.
     *
     * @param string $namePattern Pattern of key to match. `'%'` characters should be used as wildcard. Underscore match is not supported.
     * @param string $value If supplied, options will be deleted only if their value matches this value.
     */
    public static function deleteLike($namePattern, $value = null)
    {
        self::getInstance()->deleteNameLike($namePattern, $value);
    }

    public static function clearCachedOption($name)
    {
        self::getInstance()->clearCachedOptionByName($name);
    }

    /**
     * Clears the option value cache and forces a reload from the Database.
     * Used in unit tests to reset the state of the object between tests.
     *
     * @return void
     * @ignore
     */
    public static function clearCache()
    {
        $option = self::getInstance();
        $option->loaded = false;
        $option->all = array();
    }

    /**
     * @var array
     */
    private $all = array();

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * Singleton instance
     * @var \Piwik\Option
     */
    private static $instance = null;

    /**
     * Returns Singleton instance
     *
     * @return \Piwik\Option
     */
    private static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Sets the singleton instance. For testing purposes.
     *
     * @param mixed
     * @ignore
     */
    public static function setSingletonInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * Private Constructor
     */
    private function __construct()
    {
    }

    protected function clearCachedOptionByName($name)
    {
        $name = $this->trimOptionNameIfNeeded($name);
        if (isset($this->all[$name])) {
            unset($this->all[$name]);
        }
    }

    protected function getValue($name)
    {
        $name = $this->trimOptionNameIfNeeded($name);
        $this->autoload();
        if (isset($this->all[$name])) {
            return $this->all[$name];
        }

        $value = Db::fetchOne('SELECT option_value FROM `' . Common::prefixTable('option') . '` ' .
                              'WHERE option_name = ?', [$name]);

        $this->all[$name] = $value;
        return $value;
    }

    protected function setValue($name, $value, $autoLoad = 0)
    {
        $autoLoad = (int)$autoLoad;
        $name     = $this->trimOptionNameIfNeeded($name);

        $sql  = 'UPDATE `' . Common::prefixTable('option') . '` SET option_value = ?, autoload = ? WHERE option_name = ?';
        $bind = array($value, $autoLoad, $name);

        $result = Db::query($sql, $bind);

        $rowsUpdated = Db::get()->rowCount($result);

        if (! $rowsUpdated) {
            try {
                $sql  = 'INSERT IGNORE INTO `' . Common::prefixTable('option') . '` (option_name, option_value, autoload) ' .
                        'VALUES (?, ?, ?) ';
                $bind = array($name, $value, $autoLoad);

                Db::query($sql, $bind);
            } catch (\Exception $e) {
            }
        }

        $this->all[$name] = $value;
    }

    protected function deleteValue($name, $value)
    {
        $name   = $this->trimOptionNameIfNeeded($name);
        $sql    = 'DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name = ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql   .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Db::query($sql, $bind);

        $this->clearCache();
    }

    protected function deleteNameLike($name, $value = null)
    {
        $name   = $this->trimOptionNameIfNeeded($name);
        $name = $this->getNameForLike($name);

        $sql    = 'DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name LIKE ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql   .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Db::query($sql, $bind);

        $this->clearCache();
    }

    private function getNameForLike($name)
    {
        $name = str_replace('\_', '###NOREPLACE###', $name);
        $name = str_replace('_', '\_', $name);
        $name = str_replace( '###NOREPLACE###', '\_', $name);
        return $name;
    }

    protected function getNameLike($name)
    {
        $name = $this->trimOptionNameIfNeeded($name);
        $name = $this->getNameForLike($name);

        $sql  = 'SELECT option_name, option_value FROM `' . Common::prefixTable('option') . '` WHERE option_name LIKE ?';
        $bind = array($name);
        $rows = Db::fetchAll($sql, $bind);

        $result = array();
        foreach ($rows as $row) {
            $result[$row['option_name']] = $row['option_value'];
        }

        return $result;
    }

    /**
     * Initialize cache with autoload settings.
     *
     * @return void
     */
    protected function autoload()
    {
        if ($this->loaded) {
            return;
        }

        $table = Common::prefixTable('option');
        $sql   = 'SELECT option_value, option_name FROM `' . $table . '` WHERE autoload = 1';
        $all   = Db::fetchAll($sql);

        foreach ($all as $option) {
            $this->all[$option['option_name']] = $option['option_value'];
        }

        $this->loaded = true;
    }

    private function trimOptionNameIfNeeded($name)
    {
        if (strlen($name) > 191) {
            StaticContainer::get('Psr\Log\LoggerInterface')->debug("Option name '$name' is too long and was trimmed to 191 chars");
            $name = substr($name, 0, 191);
        }

        return $name;
    }
}
