<?php
/**
* Matomo - free/libre analytics platform
*
* @link https://matomo.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

namespace Piwik\Plugins\BulkTracking\tests\Framework\Mock\Tracker;


class Requests extends \Piwik\Plugins\BulkTracking\Tracker\Requests
{
    private $rawData;
    private $requiresAuth = false;

    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
    }

    public function getRawBulkRequest()
    {
        if (!is_null($this->rawData)) {
            return $this->rawData;
        }

        return parent::getRawBulkRequest();
    }

    public function enableRequiresAuth()
    {
        $this->requiresAuth = true;
    }

    public function requiresAuthentication()
    {
        return $this->requiresAuth;
    }
}
