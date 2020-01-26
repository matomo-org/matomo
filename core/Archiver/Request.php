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
        $this->setUrl($url);
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

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;

        // TODO: revisit in matomo 4
        // period=range&date=last1/period=range&date=previous1 can cause problems during archiving due to Parameters::isDayArchive()
        if (preg_match('/[&?]period=range/', $this->url)) {
            $this->changeParam('period', 'day');

            if (preg_match('/[&?]date=last1/', $this->url)) {
                $this->changeParam('date', 'today');
            } else if (preg_match('/[&?]date=previous1/', $this->url)) {
                $this->changeParam('date', 'yesterday');
            }
        }
    }

    public function changeDate($newDate)
    {
        $this->changeParam('date', $newDate);
    }

    private function changeParam($name, $newValue)
    {
        $url = $this->getUrl();
        $url = preg_replace('/([&?])' . preg_quote($name) . '=[^&]*/', '$1' . $name . '=' . $newValue, $url);
        $this->setUrl($url);
    }
}
