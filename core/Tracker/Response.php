<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Profiler;
use Piwik\Timer;
use Piwik\Tracker;
use Piwik\Tracker\Db as TrackerDb;

class Response
{
    private $timer;

    private $content;

    public function init(Tracker $tracker)
    {
        ob_start(); // we use ob_start only because of Common::printDebug, we should actually not really use ob_start

        if ($tracker->isDebugModeEnabled()) {
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
            $headerPage = str_replace('{$HTML_TITLE}', 'Piwik &rsaquo; Error', $headerPage);

            echo $headerPage . '<p>' . $this->getMessageFromException($e) . '</p>' . $trailer . $footerPage;
        } else {
            $this->outputApiResponse($tracker);
        }
    }

    public function outputResponse(Tracker $tracker)
    {
        if (!$tracker->shouldRecordStatistics()) {
            $this->outputApiResponse($tracker);
            Common::printDebug("Logging disabled, display transparent logo");
        } elseif (!$tracker->hasLoggedRequests()) {
            if (!$this->isHttpGetRequest() || !empty($_GET) || !empty($_POST)) {
                Common::sendResponseCode(400);
            }
            Common::printDebug("Empty request => Piwik page");
            echo "<a href='/'>Piwik</a> is a free/libre web <a href='http://piwik.org'>analytics</a> that lets you keep control of your data.";
        } else {
            $this->outputApiResponse($tracker);
            Common::printDebug("Nothing to notice => default behaviour");
        }

        Common::printDebug("End of the page.");

        if ($tracker->isDebugModeEnabled()
            && $tracker->isDatabaseConnected()
            && TrackerDb::isProfilingEnabled()) {
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

        if (array_key_exists('send_image', $request) && $request['send_image'] === '0') {
            Common::sendResponseCode(204);
            return;
        }

        $this->outputTransparentGif();
    }

    private function outputTransparentGif()
    {
        $transGifBase64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        Common::sendHeader('Content-Type: image/gif');

        echo base64_decode($transGifBase64);
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
            return "Error while connecting to the Piwik database - please check your credentials in config/config.ini.php file";
        }

        if (Common::isPhpCliMode()) {
            return $e->getMessage() . "\n" . $e->getTraceAsString();
        }

        return $e->getMessage();
    }

    protected function logExceptionToErrorLog(Exception $e)
    {
        error_log(sprintf("Error in Piwik (tracker): %s", str_replace("\n", " ", $this->getMessageFromException($e))));
    }
}
