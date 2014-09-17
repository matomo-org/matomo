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

class Html extends ApiRenderer
{

    public function renderException($message, \Exception $exception)
    {
        Common::sendHeader('Content-Type: text/plain; charset=utf-8', true);

        return nl2br($message);
    }

    public function renderDataTable($dataTable)
    {
        /** @var \Piwik\DataTable\Renderer\Html $tableRenderer */
        $tableRenderer = $this->buildDataTableRenderer($dataTable);
        $tableRenderer->setTableId($this->request['method']);

        $method = Common::getRequestVar('method', '', 'string', $this->request);

        $tableRenderer->setApiMethod($method);
        $tableRenderer->setIdSite(Common::getRequestVar('idSite', false, 'int', $this->request));
        $tableRenderer->setTranslateColumnNames(Common::getRequestVar('translateColumnNames', false, 'int', $this->request));

        return $tableRenderer->render();
    }

    public function renderArray($array)
    {
        return $this->renderDataTable($array);
    }

    public function sendHeader()
    {
        Common::sendHeader('Content-Type: text/html; charset=utf-8', true);
    }

}
