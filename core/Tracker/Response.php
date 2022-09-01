<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Profiler;
use Piwik\Timer;
use Piwik\Tracker;
use Piwik\Tracker\Db as TrackerDb;
use Piwik\Url;

class Response
{
    private $timer;

    private $content;

    public function init(Tracker $tracker)
    {
        ob_start(); // we use ob_start only because of Common::printDebug, we should actually not really use ob_start

        if ($tracker->isDebugModeEnabled() && TrackerConfig::getConfigValue('enable_sql_profiler')) {
            $this->timer = new Timer();

            TrackerDb::enableProfiling();
        }
    }

    public function getOutput()
    {
        $this->outputAccessControlHeaders();

        if (is_null($this->content) && ob_get_level() > 0) {
            $this->content = ob_get_clean();
        }

        return $this->content;
    }

    /**
     * Echos an error message & other information, then exits.
     *
     * @param Tracker $tracker
     * @param Exception $e
     * @param int  $statusCode eg 500
     */
    public function outputException(Tracker $tracker, Exception $e, $statusCode)
    {
        Common::sendResponseCode($statusCode);
        $this->logExceptionToErrorLog($e);

        if ($tracker->isDebugModeEnabled()) {
            Common::sendHeader('Content-Type: text/html; charset=utf-8');
            $trailer = '<span style="color: #888888">Backtrace:<br /><pre>' . $e->getTraceAsString() . '</pre></span>';
            $headerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/simpleLayoutHeader.tpl');
            $footerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/simpleLayoutFooter.tpl');
            $headerPage = str_replace('{$HTML_TITLE}', 'Matomo &rsaquo; Error', $headerPage);

            echo $headerPage . '<p>' . $this->getMessageFromException($e) . '</p>' . $trailer . $footerPage;
        } else {
            $this->outputApiResponse($tracker);
        }
    }

    public function outputResponse(Tracker $tracker)
    {
        if (!$tracker->shouldRecordStatistics()) {
            Common::sendResponseCode(503);
            $this->outputApiResponse($tracker);
            Common::printDebug("Logging disabled, display transparent logo");
        } elseif (!$tracker->hasLoggedRequests()) {
            if (!$this->isHttpGetRequest() || !empty($_GET) || !empty($_POST)) {
                Common::sendResponseCode(400);
            }
            Common::printDebug("Empty request => Matomo page");
            echo "This resource is part of Matomo. Keep full control of your data with the leading free and open source <a href='https://matomo.org' target='_blank' rel='noopener noreferrer nofollow'>web analytics & conversion optimisation platform</a>.<br>\n";
            echo "This file is the endpoint for the Matomo tracking API. If you want to access the Matomo UI or use the Reporting API, please use <a href='index.php'>index.php</a> instead.";
        } else {
            $this->outputApiResponse($tracker);
            Common::printDebug("Nothing to notice => default behaviour");
        }

        Common::printDebug("End of the page.");

        if (
            $tracker->isDebugModeEnabled()
            && $tracker->isDatabaseConnected()
            && TrackerDb::isProfilingEnabled()
        ) {
            $db = Tracker::getDatabase();
            $db->recordProfiling();
            Profiler::displayDbTrackerProfile($db);
        }

        if ($tracker->isDebugModeEnabled()) {
            Common::printDebug($_COOKIE);
            Common::printDebug((string)$this->timer);
        }
    }

    private function outputAccessControlHeaders()
    {
        if (!$this->isHttpGetRequest()) {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
            Common::sendHeader('Access-Control-Allow-Origin: ' . $origin);
            Common::sendHeader('Access-Control-Allow-Credentials: true');
        }
    }

    private function isHttpGetRequest()
    {
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        return strtoupper($requestMethod) === 'GET';
    }

    private function getOutputBuffer()
    {
        return ob_get_contents();
    }

    protected function hasAlreadyPrintedOutput()
    {
        return strlen($this->getOutputBuffer()) > 0;
    }

    private function outputApiResponse(Tracker $tracker)
    {
        if ($tracker->isDebugModeEnabled()) {
            return;
        }

        if ($this->hasAlreadyPrintedOutput()) {
            return;
        }

        $request = $_GET + $_POST;

        if ($this->isHttpGetRequest()) {
            Common::sendHeader('Cache-Control: no-store');
        }

        if (array_key_exists('send_image', $request) && $request['send_image'] === '0') {
            Common::sendResponseCode(204);
            return;
        }

        // Check for a custom tracking image
        $customImage = Config::getInstance()->Tracker['custom_image'];
        if (!empty($customImage) && $this->outputCustomImage($customImage)) {
            return;
        }

        // No custom image defined, so output the default 1x1 base64 transparent gif
        $this->outputTransparentGif();
    }

    /**
     * Output a 1px x 1px transparent gif
     */
    private function outputTransparentGif()
    {
        $transGifBase64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        Common::sendHeader('Content-Type: image/gif');

        echo base64_decode($transGifBase64);
    }

    /**
     * Output a custom tracking image
     *
     * @param string $customImage The custom image setting specified in the config
     *
     * @return bool True if the custom image was successfully output, else false
     */
    private function outputCustomImage(string $customImage): bool
    {
        $supportedMimeTypes = ['image/png', 'image/gif', 'image/jpeg'];

        $img = null;
        $size = null;

        if (strlen($customImage) > 2 && substr($customImage, -2) == '==') {
            // Base64 image string
            $img = base64_decode($customImage);
            $size = getimagesizefromstring($img);
        } elseif (is_file($customImage) && is_readable($customImage)) {
            // Image file
            $img = file_get_contents($customImage);
            $size = getimagesize($customImage); // imagesize is used to get the mime type
        }

        // Must have valid image data and a valid mime type to proceed
        if ($img && $size && isset($size['mime'])  && in_array($size['mime'], $supportedMimeTypes)) {
            Common::sendHeader('Content-Type: ' . $size['mime']);
            echo $img;
            return true;
        }

        return false;
    }

    /**
     * Gets the error message to output when a tracking request fails.
     *
     * @param Exception $e
     * @return string
     */
    protected function getMessageFromException($e)
    {
        // Note: duplicated from FormDatabaseSetup.isAccessDenied
        // Avoid leaking the username/db name when access denied
        if ($e->getCode() == 1044 || $e->getCode() == 42000) {
            return "Error while connecting to the Matomo database - please check your credentials in config/config.ini.php file";
        }

        if (Common::isPhpCliMode()) {
            return $e->getMessage() . "\n" . $e->getTraceAsString();
        }

        return $e->getMessage();
    }

    protected function logExceptionToErrorLog($e)
    {
        $hostname = Url::getRFCValidHostname();
        $hostStr = $hostname ? "[$hostname]" : '-';
        error_log(sprintf("$hostStr Error in Matomo (tracker): %s", str_replace("\n", " ", $this->getMessageFromException($e))));
    }
}
