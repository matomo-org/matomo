<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs;

use Piwik\Url;

/**
 * A Job that executes a Piwik URL.
 *
 * @api
 */
class UrlJob extends Job
{
    /**
     * The URL that the Job processor should execute.
     *
     * @var string[]|string
     */
    public $url;

    /**
     * Constructor.
     *
     * @param string[]|string $url An array of query parameters.
     */
    public function __construct($url = null)
    {
        $this->url = $url;
    }

    /**
     * The method that is executed before a job starts.
     */
    public function jobStarting()
    {
        // empty
    }

    /**
     * The method that is executed after a job finishes.
     *
     * @param string $response The string response that the job's URL returned.
     */
    public function jobFinished($response)
    {
        // empty
    }

    public function getUrlString()
    {
        $url = array_map('urlencode', $this->url);
        return '?' . Url::getQueryStringFromParameters($url);
    }
}