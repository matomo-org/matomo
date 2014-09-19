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

class Php extends ApiRenderer
{
    public function renderSuccess($message)
    {
        $success = array('result' => 'success', 'message' => $message);

        return $this->serializeIfNeeded($success);
    }

    public function renderException($message, \Exception $exception)
    {
        $message = array('result' => 'error', 'message' => $message);

        return $this->serializeIfNeeded($message);
    }

    public function renderDataTable($dataTable)
    {
        /** @var \Piwik\DataTable\Renderer\Php $tableRenderer */
        $tableRenderer = $this->buildDataTableRenderer($dataTable);

        $tableRenderer->setSerialize($this->shouldSerialize(1));
        $tableRenderer->setPrettyDisplay(Common::getRequestVar('prettyDisplay', false, 'int', $this->request));

        return $tableRenderer->render();
    }

    public function renderArray($array)
    {
        if (!Piwik::isMultiDimensionalArray($array)) {
            return $this->renderDataTable($array);
        }

        if ($this->shouldSerialize(1)) {
            return serialize($array);
        }

        return $array;
    }

    public function sendHeader()
    {
        Common::sendHeader('Content-Type: text/plain; charset=utf-8');
    }

    /**
     * Returns true if the user requested to serialize the output data (&serialize=1 in the request)
     *
     * @param mixed $defaultSerializeValue Default value in case the user hasn't specified a value
     * @return bool
     */
    private function shouldSerialize($defaultSerializeValue)
    {
        $serialize = Common::getRequestVar('serialize', $defaultSerializeValue, 'int', $this->request);

        return !empty($serialize);
    }

    private function serializeIfNeeded($response)
    {
        if ($this->shouldSerialize(1)) {
            return serialize($response);
        }
        return $response;
    }
}
