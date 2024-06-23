<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Tracker;

use Exception;
use Piwik\Tracker;

class Response extends \Piwik\Tracker\Response
{
    public $statusCode = 200;
    public $exception;
    public $isInit = false;
    public $isExceptionOutput = false;
    public $isResponseOutput = false;
    public $isSend = false;

    private $output = 'My Dummy Content';

    public function init(Tracker $tracker)
    {
        $this->isInit = true;
    }

    public function getOutput()
    {
        $this->isSend = true;

        return $this->output;
    }

    public function outputException(Tracker $tracker, Exception $e, $statusCode)
    {
        $this->isExceptionOutput = true;
        $this->statusCode = $statusCode;
        $this->exception = $e;

        $this->output = $e->getMessage();
    }

    public function outputResponse(Tracker $tracker)
    {
        $this->isResponseOutput = true;
    }
}
