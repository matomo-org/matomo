<?php
/**
* Piwik - free/libre analytics platform
*
* @link http://piwik.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

namespace Piwik\Tests\Framework\Mock\Tracker;

use Exception;
use Piwik\Tracker;

class RequestSet extends \Piwik\Tracker\RequestSet
{
    private $throwExceptionOnInit = false;

    public function enableThrowExceptionOnInit()
    {
        $this->throwExceptionOnInit = true;
    }

    public function initRequestsAndTokenAuth()
    {
        if ($this->throwExceptionOnInit) {
            throw new Exception('Init requests and token auth exception', 493);
        }

        parent::initRequestsAndTokenAuth();
    }

}
