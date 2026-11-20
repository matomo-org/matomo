<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\Renderer;

use Piwik\API\ApiRenderer;
use Piwik\Common;

class Rss extends ApiRenderer
{
    /**
     * @param $message
     * @param \Exception|\Throwable $exception
     * @return string
     */
    public function renderException($message, $exception)
    {
        Common::sendHeader('Content-Type: text/plain; charset=utf-8', true);

        return 'Error: ' . $this->messageAsPlainText($message);
    }

    public function renderDataTable($dataTable)
    {
        /** @var \Piwik\DataTable\Renderer\Rss $tableRenderer */
        $tableRenderer = $this->buildDataTableRenderer($dataTable);

        $idSite = $this->requestObj->getIntegerParameter('idSite', 0);
        $method = Common::sanitizeInputValue($this->requestObj->getStringParameter('method', ''));

        if (empty($idSite)) {
            $idSite = 'all';
        }

        $tableRenderer->setApiMethod($method);
        $tableRenderer->setIdSite($idSite);
        $tableRenderer->setTranslateColumnNames($this->requestObj->getBoolParameter('translateColumnNames', false));

        return $tableRenderer->render();
    }

    public function renderArray($array)
    {
        return $this->renderDataTable($array);
    }

    public function sendHeader()
    {
        Common::sendHeader('Content-Type: text/xml; charset=utf-8');
    }
}
