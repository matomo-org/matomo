<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

/**
 * Registry class.
 *
 * @method static \Piwik\Registry getInstance()
 */
class Registry extends Singleton
{
    private $data;

    protected function __construct()
    {
        $this->data = array();
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
