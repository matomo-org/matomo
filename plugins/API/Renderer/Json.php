<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API\Renderer;

use Piwik\API\ApiRenderer;
use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\ProxyHttp;

/**
 * API output renderer for JSON.
 *
 * **NOTE: This is the old JSON format. It includes bugs that are fixed in the JSON2 API output
 * format. Please use that format instead of this.**
 *
 * @deprecated
 */
class Json extends ApiRenderer
{
    public function renderSuccess($message)
    {
        $result = json_encode(array('result' => 'success', 'message' => $message));
        return $this->applyJsonpIfNeeded($result);
    }

    public function renderException($message, \Exception $exception)
    {
        $exceptionMessage = str_replace(array("\r\n", "\n"), "", $message);

        $result = json_encode(array('result' => 'error', 'message' => $exceptionMessage));

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
            return $this->applyJsonpIfNeeded($result);
        }

        return $this->renderDataTable($array);
    }

    public function sendHeader()
    {
        Renderer\Json::sendHeaderJSON();
        ProxyHttp::overrideCacheControlHeaders();
    }

    /**
     * @param $str
     * @return string
     */
    private function applyJsonpIfNeeded($str)
    {
        $jsonCallback = Common::getRequestVar('callback', false, null, $this->request);

        if ($jsonCallback === false) {
            $jsonCallback = Common::getRequestVar('jsoncallback', false, null, $this->request);
        }

        if ($jsonCallback !== false) {
            if (preg_match('/^[0-9a-zA-Z_.]*$/D', $jsonCallback) > 0) {
                $str = $jsonCallback . "(" . $str . ")";
            }
        }

        return $str;
    }
}
