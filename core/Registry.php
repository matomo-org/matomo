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
 * Registry class.
 *
 * @package Piwik
 */
class Registry
{
    private static $instance;
    private $data;

    private function __construct()
    {
        $this->data = array();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Registry();
        }
        return self::$instance;
    }

    public static function isRegistered($key)
    {
        return self::getInstance()->hasKey($key);
    }

    public static function get($key)
    {
        return self::getInstance()->getKey($key);
    }

    public static function set($key, $value)
    {
        self::getInstance()->setKey($key, $value);
    }

    public static function unsetInstance()
    {
        self::$instance = null;
    }

    public function setKey($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getKey($key)
    {
        if (!$this->hasKey($key)) {
            throw new \Exception(sprintf("Key '%s' doesn't exist in Registry", $key));
        }
        return $this->data[$key];
    }

    public function hasKey($key)
    {
        return array_key_exists($key, $this->data);
    }
}
