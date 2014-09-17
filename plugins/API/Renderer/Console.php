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

class Console extends ApiRenderer
{

    public function renderException($message, \Exception $exception)
    {
        self::sendHeader();

        return 'Error: ' . $message;
    }

    public function sendHeader()
    {
        Common::sendHeader('Content-Type: text/plain; charset=utf-8');
    }

}
