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

class Console extends ApiRenderer
{

    /**
     * @param $message
     * @param \Exception|\Throwable $exception
     * @return string
     */
    public function renderException($message, $exception)
    {
        self::sendHeader();

        return 'Error: ' . $message;
    }

    public function sendHeader()
    {
        Common::sendHeader('Content-Type: text/plain; charset=utf-8');
    }

}
