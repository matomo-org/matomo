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

class Rss extends ApiRenderer
{

    public function renderException($message, \Exception $exception)
    {
        self::sendHeader('plain');

        return 'Error: ' . $message;
    }

    public function renderDataTable($dataTable)
    {
        /** @var \Piwik\DataTable\Renderer\Rss $tableRenderer */
        $tableRenderer = $this->buildDataTableRenderer($dataTable);

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

    public function sendHeader($type = "xml")
    {
        Common::sendHeader('Content-Type: text/' . $type . '; charset=utf-8');
    }


}
