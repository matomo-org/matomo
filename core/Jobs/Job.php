<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs;

use Exception;
use Piwik\Log;

/**
 * TODO
 */
class Job
{
    /**
     * TODO
     *
     * @var string
     */
    public $url;

    /**
     * TODO
     *
     * @var array
     */
    public $onJobStarting;

    /**
     * TODO
     *
     * @var array
     */
    public $onJobFinished;

    /**
     * TODO
     */
    public function __construct($url = null, $onJobStarting = null, $onJobFinished = null)
    {
        $this->url = $url;
        $this->onJobStarting = $onJobStarting;
        $this->onJobFinished = $onJobFinished;
    }

    /**
     * TODO
     */
    public function jobStarting()
    {
        try {
            $this->validate();
        } catch (Exception $ex) {
            Log::warning("Job::%s: Invalid job encountered: '%s' (url = '%s').", __FUNCTION__, $ex->getMessage(), $this->url);
            return;
        }

        if (!empty($this->onJobStarting)) {
            $callback = $this->onJobStarting['callback'];
            $params = empty($this->onJobStarting['params']) ? array() : $this->onJobStarting['params'];

            call_user_func_array($callback, $params);
        }
    }

    /**
     * TODO
     */
    public function jobFinished($response)
    {
        try {
            $this->validate();
        } catch (Exception $ex) {
            Log::warning("Job::%s: Invalid job encountered: '%s' (url = '%s').", __FUNCTION__, $ex->getMessage(), $this->url);
            return;
        }

        if (!empty($this->onJobFinished)) {
            $callback = $this->onJobFinished['callback'];
            $params = empty($this->onJobFinished['params']) ? array() : $this->onJobFinished['params']; // TODO: code redundancy w/ above

            $params = array_merge(array($response), $params);

            call_user_func_array($callback, $params);
        }
    }

    /**
     * TODO
     */
    public function validate()
    {
        if (empty($this->url)
            || !is_string($this->url)
        ) {
            throw new Exception("Invalid Job URL: " . var_export($this->url, true));
        }

        $this->validateDistributedCallback($this->onJobStarting,"onJobStarting");
        $this->validateDistributedCallback($this->onJobFinished, "onJobFinished");
    }

    private function validateDistributedCallback($callback, $name)
    {
        if (empty($callback)) {
            return;
        }

        if (empty($callback['callback'])) {
            throw new Exception("No callback property in '$name' callback.");
        }

        if (!$this->isStringArray($callback['callback'])) {
            throw new Exception("Invalid Job callback for '$name': callback must be an array of strings.");
        }
    }

    private function isStringArray($value) {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $element) {
            if (!is_string($element)) {
                return false;
            }
        }

        return true;
    }
}