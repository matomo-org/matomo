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
        $values = include(self::getCachePath($userAgent));
        $this->bot = $values['bot'];
        $this->brand = $values['brand'];
        $this->client = $values['client'];
        $this->device = $values['device'];
        $this->model = $values['model'];
        $this->os = $values['os'];
    }

    public static function isCached($userAgent)
    {
        return file_exists(self::getCachePath($userAgent));
    }

    public static function getCachePath($userAgent)
    {
        $hashedUserAgent = md5($userAgent);
        // We use hash subdirs to prevent adding too many files to the same dir
        $hashDir = PIWIK_DOCUMENT_ROOT . self::CACHE_DIR . substr($hashedUserAgent, 0, 2);
        if (!file_exists($hashDir)) {
            mkdir($hashDir);
        }
        return $hashDir . '/' . $hashedUserAgent . '.php';
    }
}