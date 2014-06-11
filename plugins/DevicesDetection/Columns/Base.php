<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Columns;

use \DeviceDetector;
use Piwik\CacheFile;
use Piwik\Plugin\VisitDimension;

abstract class Base extends VisitDimension
{
    private static $uaParser = array();

    /**
     * @param  string $userAgent
     * @return DeviceDetector
     */
    public function getUAParser($userAgent)
    {
        $key = md5($userAgent);

        if (!array_key_exists($key, self::$uaParser)) {

            $UAParser = new \DeviceDetector($userAgent);
            $UAParser->setCache(new CacheFile('tracker', 86400));
            $UAParser->parse();

            self::$uaParser[$key] = $UAParser;
        }

        return self::$uaParser[$key];
    }
}
