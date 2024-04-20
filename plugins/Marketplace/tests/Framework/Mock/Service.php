<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Framework\Mock;

use Piwik\Filesystem;

class Service extends \Piwik\Plugins\Marketplace\Api\Service
{
    public $action;
    public $params;
    public $postData;

    private $fixtureToReturn;
    private $exception;
    private $onFetchCallback;
    private $onDownloadCallback;

    public function __construct()
    {
        parent::__construct('http://plugins.piwik.org');
    }

    /**
     * Will cause the service to throw an exception when any data is fetched
     * @param $exception
     */
    public function throwException($exception)
    {
        $this->exception = $exception;
        $this->fixtureToReturn = null;
    }

    /**
     * Will cause the service to use the content of the given fixture as a response of the plugins API.
     * Should be either a filename of a file within the "plugins/Marketplace/tests/resources/" directory or
     * an array of filenames. An array is useful if the service gets called multiple times and you want to return
     * different results for each API call. If an array is given, first filename will be returned first, then next, ...
     *
     * @param string|array $fixtureName
     */
    public function returnFixture($fixtureName)
    {
        $this->fixtureToReturn = $fixtureName;
        $this->exception = null;
    }

    public function download(
        $url,
        $destinationPath = null,
        $timeout = null,
        ?array $postData = null,
        bool $getExtendedInfo = false
    ) {
        if ($this->onDownloadCallback && is_callable($this->onDownloadCallback)) {
            $result = call_user_func(
                $this->onDownloadCallback,
                $this->action,
                $this->params,
                $this->postData
            );

            if ($getExtendedInfo && is_string($result)) {
                return [
                    'status' => 200,
                    'headers' => [],
                    'data' => $result,
                ];
            }

            if (null !== $result) {
                return $result;
            }
        }

        if ($destinationPath) {
            Filesystem::mkdir(@dirname($destinationPath));
            file_put_contents($destinationPath, $url);
            return true;
        }

        if (!empty($this->fixtureToReturn)) {
            if (is_array($this->fixtureToReturn)) {
                $fixture = array_shift($this->fixtureToReturn);
            } else {
                $fixture = $this->fixtureToReturn;
                $this->fixtureToReturn = null;
            }

            $data = $this->getFixtureContent($fixture);

            if ($getExtendedInfo) {
                return [
                    'status' => 200,
                    'headers' => [],
                    'data' => $data,
                ];
            }

            return $data;
        }
    }

    public function getFixtureContent($fixture)
    {
        $path = PIWIK_INCLUDE_PATH . '/plugins/Marketplace/tests/resources/' . $fixture;

        return file_get_contents($path);
    }

    // here you can set a custom callback and record all actions/ params and even return a custom result for each
    // action / params if wanted
    public function setOnFetchCallback($callback)
    {
        $this->onFetchCallback = $callback;
    }

    // here you can set a custom callback and record all actions/ params and even return a custom result for each
    // action / params if wanted
    public function setOnDownloadCallback($callback)
    {
        $this->onDownloadCallback = $callback;
    }

    public function fetch(
        $action,
        $params,
        ?array $postData = null,
        bool $getExtendedInfo = false,
        bool $throwOnApiError = true
    ) {
        $this->action = $action;
        $this->params = $params;
        $this->postData = $postData;

        if ($this->onFetchCallback && is_callable($this->onFetchCallback)) {
            $result = call_user_func($this->onFetchCallback, $action, $params, $postData);

            if (null !== $result) {
                return $result;
            }
        }

        if (isset($this->exception)) {
            throw $this->exception;
        } elseif (!empty($this->fixtureToReturn) || $this->onDownloadCallback) {
            // we want to make sure to test as much of the service class as possible.
            // Therefore we only mock the HTTP request in download()
            return parent::fetch($action, $params, $postData, $getExtendedInfo, $throwOnApiError);
        }

        return [];
    }
}
