<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API;

use Exception;
use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\SettingsServer;

/**
 * API renderer
 */
abstract class ApiRenderer
{
    /** @var array */
    protected $request;

    /** @var \Piwik\Request */
    protected $requestObj;

    /** @var bool */
    protected $hideIdSubDataTable;

    /** @var bool */
    protected $showMetadata;

    final public function __construct($request)
    {
        $this->request = is_array($request) ? $request : [];
        $this->requestObj = new \Piwik\Request($this->request);
        $this->init();
    }

    protected function init()
    {
        $this->hideIdSubDataTable = $this->requestObj->getBoolParameter('hideIdSubDatable', false);
        $this->showMetadata = $this->requestObj->getBoolParameter('showMetadata', true); // @todo change default in Matomo 6
    }

    protected function shouldSendBacktrace(): bool
    {
        return Common::isPhpCliMode() && SettingsServer::isArchivePhpTriggered();
    }

    abstract public function sendHeader();

    public function renderSuccess($message)
    {
        return 'Success:' . $message;
    }

    /**
     * @param $message
     * @param Exception|\Throwable $exception
     * @return mixed
     */
    public function renderException($message, $exception)
    {
        return $message;
    }

    public function renderScalar($scalar)
    {
        $dataTable = new DataTable\Simple();
        $dataTable->addRowsFromArray(array($scalar));
        return $this->renderDataTable($dataTable);
    }

    public function renderDataTable($dataTable)
    {
        $renderer = $this->buildDataTableRenderer($dataTable);
        return $renderer->render();
    }

    public function renderArray($array)
    {
        $renderer = $this->buildDataTableRenderer($array);
        return $renderer->render();
    }

    public function renderObject($object)
    {
        $exception = new Exception('The API cannot handle this data structure.');
        return $this->renderException($exception->getMessage(), $exception);
    }

    public function renderResource($resource)
    {
        $exception = new Exception('The API cannot handle this data structure.');
        return $this->renderException($exception->getMessage(), $exception);
    }

    /**
     * @param $dataTable
     * @return Renderer
     */
    protected function buildDataTableRenderer($dataTable)
    {
        $format   = self::getFormatFromClass(get_class($this));

        $idSite = $this->requestObj->getIntegerParameter('idSite', 0);

        if (empty($idSite)) {
            $idSite = 'all';
        }

        $renderer = Renderer::factory($format);
        $renderer->setTable($dataTable);
        $renderer->setIdSite($idSite);
        $renderer->setRenderSubTables($this->requestObj->getBoolParameter('expanded', false));
        $renderer->setHideIdSubDatableFromResponse($this->hideIdSubDataTable);
        $renderer->setHideMetadataFromResponse(!$this->showMetadata);

        return $renderer;
    }

    /**
     * @param string $format
     * @param array  $request
     * @return ApiRenderer
     * @throws Exception
     */
    public static function factory(string $format, array $request): ApiRenderer
    {
        $formatToCheck = '\\' . ucfirst(strtolower($format));

        $rendererClassnames = Plugin\Manager::getInstance()->findMultipleComponents('Renderer', 'Piwik\\API\\ApiRenderer');

        foreach ($rendererClassnames as $klassName) {
            if (Common::stringEndsWith($klassName, $formatToCheck)) {
                return new $klassName($request);
            }
        }

        $availableRenderers = array();
        foreach ($rendererClassnames as $rendererClassname) {
            $availableRenderers[] = self::getFormatFromClass($rendererClassname);
        }

        $availableRenderers = implode(', ', $availableRenderers);
        Common::sendHeader('Content-Type: text/plain; charset=utf-8');
        throw new Exception(Piwik::translate('General_ExceptionInvalidRendererFormat', array($format, $availableRenderers)));
    }

    private static function getFormatFromClass(string $klassname): string
    {
        $klass = explode('\\', $klassname);

        return strtolower(end($klass));
    }
}
