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

class Original extends ApiRenderer
{
    public function renderSuccess($message)
    {
        return true;
    }

    public function renderException($message, \Exception $exception)
    {
        throw $exception;
    }

    public function renderDataTable($dataTable)
    {
        return $this->serializeIfNeeded($dataTable);
    }

    public function renderArray($array)
    {
        return $this->serializeIfNeeded($array);
    }

    public function renderScalar($scalar)
    {
        return $scalar;
    }

    public function renderObject($object)
    {
        return $object;
    }

    public function renderResource($resource)
    {
        return $resource;
    }

    public function sendHeader()
    {
        if ($this->shouldSerialize()) {
            Common::sendHeader('Content-Type: text/plain; charset=utf-8');
        }
    }

    /**
     * Returns true if the user requested to serialize the output data (&serialize=1 in the request)
     *
     * @return bool
     */
    private function shouldSerialize()
    {
        $serialize = Common::getRequestVar('serialize', 0, 'int', $this->request);

        return !empty($serialize);
    }

    private function serializeIfNeeded($response)
    {
        if ($this->shouldSerialize()) {
            return serialize($response);
        }
        return $response;
    }
}
