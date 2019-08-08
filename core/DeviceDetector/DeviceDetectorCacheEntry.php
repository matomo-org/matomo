<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DeviceDetector;

use DeviceDetector\DeviceDetector;

class DeviceDetectorCacheEntry extends DeviceDetector
{
    const CACHE_DIR = "/misc/useragents/";

    public function __construct($userAgent)
    {
        parent::setUserAgent($userAgent);
        $this->client = include(self::getCachePath($userAgent));
    }

    public static function isCached($userAgent)
    {
        return file_exists(self::getCachePath($userAgent));
    }

    public static function getCachePath($userAgent)
    {
        return PIWIK_DOCUMENT_ROOT . self::CACHE_DIR . md5($userAgent) . '.php';
    }
}