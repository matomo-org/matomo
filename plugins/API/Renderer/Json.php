<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API\Renderer;

use Piwik\API\ApiRenderer;
use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\Piwik;
use Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor;
use Piwik\ProxyHttp;

/**
 * API output renderer for JSON.
 */
class Json extends ApiRenderer
{
    public function renderSuccess($message)
    {
        $result = json_encode(array('result' => 'success', 'message' => $message));
        return $this->applyJsonpIfNeeded($result);
    }

    /**
     * @param $message
     * @param \Exception|\Throwable $exception
     * @return string
     */
    public function renderException($message, $exception)
    {
        $exceptionMessage = str_replace(array("\r\n", "\n"), " ", $message);

        $data = array('result' => 'error', 'message' => $exceptionMessage);

        if ($this->shouldSendBacktrace()) {
            $data['backtrace'] = ExceptionToTextProcessor::getMessageAndWholeBacktrace($exception, true);
        }

        $result = json_encode($data);

        return $this->applyJsonpIfNeeded($result);
    }

    public function renderDataTable($dataTable)
    {
        $result = parent::renderDataTable($dataTable);

        return $this->applyJsonpIfNeeded($result);
    }

    public function renderArray($array)
    {
        if (Piwik::isMultiDimensionalArray($array)) {
            $jsonRenderer = Renderer::factory('json');
            $jsonRenderer->setTable($array);
            $result = $jsonRenderer->render();
        } else {
            $result = parent::renderDataTable($array);

            // if $array is a simple associative array, remove the JSON root array that is added by renderDataTable
            if (!empty($array) && Piwik::isAssociativeArray($array)) {
                $result = substr($result, 1, strlen($result) - 2);
            }
        }

        return $this->applyJsonpIfNeeded($result);
    }

    public function sendHeader()
    {
        if ($this->isJsonp()) {
            Common::sendHeader('Content-Type: application/javascript; charset=utf-8');
        } else {
            Renderer\Json::sendHeaderJSON();
        }

        ProxyHttp::overrideCacheControlHeaders();
    }

    private function isJsonp()
    {
        $callback = $this->getJsonpCallback();

        if (false === $callback) {
            return false;
        }

        return preg_match('/^[0-9a-zA-Z_.]*$/D', $callback) > 0;
    }

    private function getJsonpCallback()
    {
        $jsonCallback = Common::getRequestVar('callback', false, null, $this->request);

        if ($jsonCallback === false) {
            $jsonCallback = Common::getRequestVar('jsoncallback', false, null, $this->request);
        }

        return $jsonCallback;
    }

    /**
     * @param $str
     * @return string
     */
    private function applyJsonpIfNeeded($str)
    {
        if ($this->isJsonp()) {
            $jsonCallback = $this->getJsonpCallback();
            $str = $jsonCallback . "(" . $str . ")";
        }

        return $str;
    }
}
