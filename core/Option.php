<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

/**
 * Option provides a very simple mechanism to save/retrieve key-values pair
 * from the database (persistent key-value datastore).
 *
 * This is useful to save Piwik-wide preferences, configuration values.
 *
 * @package Piwik
 */
class Option
{
    /**
     * Returns the option value for the requested option $name, fetching from database, if not in cache.
     *
     * @param string $name Key
     * @return string|bool  Value or false, if not found
     */
    public static function get($name)
    {
        return self::getInstance()->getValue($name);
    }

    /**
     * Sets the option value in the database and cache
     *
     * @param string $name
     * @param string $value
     * @param int $autoLoad if set to 1, this option value will be automatically loaded; should be set to 1 for options that will always be used in the Piwik request.
     */
    public static function set($name, $value, $autoload = 0)
    {
        return self::getInstance()->setValue($name, $value, $autoload);
    }

    /**
     * Delete key-value pair from database and reload cache.
     *
     * @param string $name Key to match exactly
     * @param string $value Optional value
     */
    public static function delete($name, $value = null)
    {
        return self::getInstance()->deleteValue($name, $value);
    }

    /**
     * Delete key-value pair(s) from database and reload cache.
     * The supplied pattern should use '%' as wildcards, and literal '_' should be escaped.
     *
     * @param string $name Pattern of key to match.
     * @param string $value Optional value
     */
    public static function deleteLike($name, $value = null)
    {
        return self::getInstance()->deleteNameLike($name, $value);
    }

    /**
     * Clears the cache
     * Used in unit tests to reset the state of the object between tests
     *
     * @return void
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
    static private $instance = null;

    /**
     * Returns Singleton instance
     *
     * @return \Piwik\Option
     */
    static private function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Private Constructor
     */
    private function __construct()
    {
    }

    protected function getValue($name)
    {
        $this->autoload();
        if (isset($this->all[$name])) {
            return $this->all[$name];
        }
        $value = Db::fetchOne('SELECT option_value ' .
            'FROM `' . Common::prefixTable('option') . '`' .
            'WHERE option_name = ?', $name);
        if ($value === false) {
            return false;
        }
        $this->all[$name] = $value;
        return $value;
    }

    protected function setValue($name, $value, $autoLoad = 0)
    {
        $autoLoad = (int)$autoLoad;
        Db::query('INSERT INTO `' . Common::prefixTable('option') . '` (option_name, option_value, autoload) ' .
            ' VALUES (?, ?, ?) ' .
            ' ON DUPLICATE KEY UPDATE option_value = ?',
            array($name, $value, $autoLoad, $value));
        $this->all[$name] = $value;
    }

    protected function deleteValue($name, $value)
    {
        $sql = 'DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name = ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Db::query($sql, $bind);

        $this->clearCache();
    }

    protected function deleteNameLike($name, $value = null)
    {
        $sql = 'DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name LIKE ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Db::query($sql, $bind);

        $this->clearCache();
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

        $all = Db::fetchAll('SELECT option_value, option_name
								FROM `' . Common::prefixTable('option') . '`
								WHERE autoload = 1');
        foreach ($all as $option) {
            $this->all[$option['option_name']] = $option['option_value'];
        }

        $this->loaded = true;
    }

}
