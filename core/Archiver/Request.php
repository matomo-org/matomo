<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Archiver;

class Request
{
    /**
     * If a request is aborted, the response of a CliMutli job will be a serialized array containing the
     * key/value "aborted => 1".
     */
    const ABORT = 'abort';

    /**
     * @var string
     */
    private $url;

    /**
     * @var callable|null
     */
    private $before;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function before($callable)
    {
        $this->before = $callable;
    }

    public function start()
    {
        if ($this->before) {
            return call_user_func($this->before);
        }
    }

    public function __toString()
    {
        return $this->url;
    }
}
