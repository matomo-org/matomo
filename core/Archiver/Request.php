<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Archiver;

class Request
{
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
            $callable = $this->before;
            $callable();
        }
    }

    public function __toString()
    {
        return $this->url;
    }
}
