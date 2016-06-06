<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Container\StaticContainer;

/**
 * Registry class.
 *
 * @method static Registry getInstance()
 * @api
 * @deprecated This class will be removed, use the container instead.
 */
class Registry extends Singleton
{
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
        if ($key === 'auth') {
            $key = 'Piwik\Auth';
        }

        StaticContainer::getContainer()->set($key, $value);
    }

    public function getKey($key)
    {
        if ($key === 'auth') {
            $key = 'Piwik\Auth';
        }

        return StaticContainer::get($key);
    }

    public function hasKey($key)
    {
        if ($key === 'auth') {
            $key = 'Piwik\Auth';
        }

        return StaticContainer::getContainer()->has($key);
    }
}
