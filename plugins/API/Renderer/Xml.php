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

class Xml extends ApiRenderer
{

    public function renderSuccess($message)
    {
        return "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" .
               "<result>\n" .
               "\t<success message=\"" . $message . "\" />\n" .
               "</result>";
    }

    /**
     * @param $message
     * @param \Exception|\Throwable $exception
     * @return string
     */
    public function renderException($message, $exception)
    {
        return '<?xml version="1.0" encoding="utf-8" ?>' . "\n" .
               "<result>\n" .
               "\t<error message=\"" . $message . "\" />\n" .
               "</result>";
    }

    public function sendHeader()
    {
        Common::sendHeader('Content-Type: text/xml; charset=utf-8');
    }

}
