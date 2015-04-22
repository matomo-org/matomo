<?php

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
