<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Http;
use Piwik\Piwik;
use Piwik\Container\StaticContainer;
use Piwik\DeviceDetector\DeviceDetectorFactory;
use Piwik\Exception\NotSupportedBrowserException;

class SupportedBrowser
{
    /**
     * A list of browsers with version numbers that are not supported. A browser
     * is not supported if it's included in this array and the version number
     * is smaller or equal to the number in this array. If the user's browser
     * not included in this list or the version number is higher, then
     * supported.
     *
     * Current version numbers are coming from this list:
     * https://caniuse.com/rel-noreferrer
     */
    private static $notSupportedBrowsers = [
        'FF' => 32,
        'IE' => 10,
        'SF' => 4,
        'CH' => 15,
        'OP' => 12,
        'PS' => 12,
    ];

    public static function checkIfBrowserSupported()
    {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if ($userAgent === '') {
            return;
        }

        $ddFactory = StaticContainer::get(DeviceDetectorFactory::class);
        /** @var \DeviceDetector\DeviceDetector */
        $deviceDetector = $ddFactory->makeInstance($userAgent);

        $deviceDetector->parse();
        $client = $deviceDetector->getClient();

        if ($client['type'] === 'browser' && self::browserNotSupported($client['short_name'], (int)$client['version'])) {
            self::throwException();
        }
    }

    private static function browserNotSupported($shortName, $version)
    {
        return array_key_exists($shortName, self::$notSupportedBrowsers) && $version > 0 && $version <= self::$notSupportedBrowsers[$shortName];
    }

    private static function throwException()
    {
        $message  = "<p><b>" . Piwik::translate('General_ExceptionNotSupportedBrowserTitle') . "</b></p>";
        $message .= "<p>" . Piwik::translate('General_ExceptionNotSupportedBrowserText') . "</p>";

        $exception = new NotSupportedBrowserException($message);
        $exception->setIsHtmlMessage();

        throw $exception;
    }
}
