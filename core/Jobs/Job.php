<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs;

use Exception;

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
     * @var string[]
     */
    public $onJobStarting;

    /**
     * TODO
     *
     * @var string[]
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

        if (!$this->isStringArray($callback)) {
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