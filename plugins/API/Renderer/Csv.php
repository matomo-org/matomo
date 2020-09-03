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
use Piwik\ProxyHttp;

class Csv extends ApiRenderer
{

    public function renderSuccess($message)
    {
        Common::sendHeader("Content-Disposition: attachment; filename=piwik-report-export.csv");
        return "message\n" . $message;
    }

    /**
     * @param $message
     * @param \Exception|\Throwable $exception
     * @return string
     */
    public function renderException($message, $exception)
    {
        Common::sendHeader('Content-Type: text/html; charset=utf-8', true);
        return 'Error: ' . $message;
    }

    public function renderDataTable($dataTable)
    {
        $convertToUnicode = Common::getRequestVar('convertToUnicode', true, 'int', $this->request);
        $idSite = Common::getRequestVar('idSite', 0, 'int', $this->request);

        if (empty($idSite)) {
            $idSite = 'all';
        }

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
