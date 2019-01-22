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
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;

class Original extends ApiRenderer
{
    public function renderSuccess($message)
    {
        return true;
    }

    /**
     * @param $message
     * @param \Exception|\Throwable $exception
     * @throws \Exception|\Throwable
     * @return string
     */
    public function renderException($message, $exception)
    {
        if ($this->shouldSerialize()) {
            return serialize([
                'result' => 'error',
                'message' => $message,
            ]);
        }

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
        return $this->serializeIfNeeded($scalar);
    }

    public function renderObject($object)
    {
        return $this->serializeIfNeeded($object);
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
            if ($response instanceof DataTableInterface) {
                // remove COLUMN_AGGREGATION_OPS_METADATA_NAME metadata since it can have closures
                $response->filter(function (DataTable $table) {
                    $allMetadata = $table->getAllTableMetadata();
                    unset($allMetadata[DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME]);
                    $table->setAllTableMetadata($allMetadata);
                });
            }

            return serialize($response);
        }
        return $response;
    }
}
