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

/**
 * Piwik_Option provides a very simple mechanism to save/retrieve key-values pair
 * from the database (persistent key-value datastore).
 *
 * This is useful to save Piwik-wide preferences, configuration values.
 *
 * @package Piwik
 */
class Piwik_Option
{
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
     * @var self
     */
    static private $instance = null;

    /**
     * Returns Singleton instance
     *
     * @return Piwik_Option
     */
    static public function getInstance()
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

    /**
     * Returns the option value for the requested option $name, fetching from database, if not in cache.
     *
     * @param string $name  Key
     * @return string|false  Value or false, if not found
     */
    public function get($name)
    {
        $this->autoload();
        if (isset($this->all[$name])) {
            return $this->all[$name];
        }
        $value = Piwik_FetchOne('SELECT option_value ' .
            'FROM `' . Piwik_Common::prefixTable('option') . '`' .
            'WHERE option_name = ?', $name);
        if ($value === false) {
            return false;
        }
        $this->all[$name] = $value;
        return $value;
    }

    /**
     * Sets the option value in the database and cache
     *
     * @param string $name
     * @param string $value
     * @param int $autoload  if set to 1, this option value will be automatically loaded; should be set to 1 for options that will always be used in the Piwik request.
     */
    public function set($name, $value, $autoload = 0)
    {
        $autoload = (int)$autoload;
        Piwik_Query('INSERT INTO `' . Piwik_Common::prefixTable('option') . '` (option_name, option_value, autoload) ' .
                ' VALUES (?, ?, ?) ' .
                ' ON DUPLICATE KEY UPDATE option_value = ?',
            array($name, $value, $autoload, $value));
        $this->all[$name] = $value;
    }

    /**
     * Delete key-value pair from database and reload cache.
     *
     * @param string $name   Key to match exactly
     * @param string $value  Optional value
     */
    public function delete($name, $value = null)
    {
        $sql = 'DELETE FROM `' . Piwik_Common::prefixTable('option') . '` WHERE option_name = ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Piwik_Query($sql, $bind);

        $this->clearCache();
    }

    /**
     * Delete key-value pair(s) from database and reload cache.
     * The supplied pattern should use '%' as wildcards, and literal '_' should be escaped.
     *
     * @param string $name   Pattern of key to match.
     * @param string $value  Optional value
     */
    public function deleteLike($name, $value = null)
    {
        $sql = 'DELETE FROM `' . Piwik_Common::prefixTable('option') . '` WHERE option_name LIKE ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Piwik_Query($sql, $bind);

        $this->clearCache();
    }

    /**
     * Initialize cache with autoload settings.
     *
     * @return void
     */
    private function autoload()
    {
        if ($this->loaded) {
            return;
        }

        $all = Piwik_FetchAll('SELECT option_value, option_name
								FROM `' . Piwik_Common::prefixTable('option') . '`
								WHERE autoload = 1');
        foreach ($all as $option) {
            $this->all[$option['option_name']] = $option['option_value'];
        }

        $this->loaded = true;
    }

    /**
     * Clears the cache
     * Used in unit tests to reset the state of the object between tests
     *
     * @return void
     */
    public function clearCache()
    {
        $this->loaded = false;
        $this->all = array();
    }
}

/**
 * Returns the option value for the requested option $name
 *
 * @param string $name  Key
 * @return string|false  Value or false, if not found
 */
function Piwik_GetOption($name)
{
    return Piwik_Option::getInstance()->get($name);
}

/**
 * Sets the option value in the database
 *
 * @param string $name
 * @param string $value
 * @param int $autoload  if set to 1, this option value will be automatically loaded; should be set to 1 for options that will always be used in the Piwik request.
 */
function Piwik_SetOption($name, $value, $autoload = 0)
{
    Piwik_Option::getInstance()->set($name, $value, $autoload);
}
