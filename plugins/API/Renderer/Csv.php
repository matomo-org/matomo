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
use Piwik\ProxyHttp;

class Csv extends ApiRenderer
{

    public function renderSuccess($message)
    {
        Common::sendHeader("Content-Disposition: attachment; filename=piwik-report-export.csv");
        return "message\n" . $message;
    }

    public function renderException($message, \Exception $exception)
    {
        Common::sendHeader('Content-Type: text/html; charset=utf-8', true);
        return 'Error: ' . $message;
    }

    public function renderDataTable($dataTable)
    {
        $convertToUnicode = Common::getRequestVar('convertToUnicode', true, 'int', $this->request);
        $idSite = Common::getRequestVar('idSite', false, 'int', $this->request);

        /** @var \Piwik\DataTable\Renderer\Csv $tableRenderer */
        $tableRenderer = $this->buildDataTableRenderer($dataTable);
        $tableRenderer->setConvertToUnicode($convertToUnicode);

        $method = Common::getRequestVar('method', '', 'string', $this->request);

        $tableRenderer->setApiMethod($method);
        $tableRenderer->setIdSite($idSite);
        $tableRenderer->setTranslateColumnNames(Common::getRequestVar('translateColumnNames', false, 'int', $this->request));

        return $tableRenderer->render();
    }

    public function renderArray($array)
    {
        return $this->renderDataTable($array);
    }

    public function sendHeader()
    {
        Common::sendHeader("Content-Type: application/vnd.ms-excel", true);
        ProxyHttp::overrideCacheControlHeaders();
    }
}
