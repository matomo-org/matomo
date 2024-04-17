<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Mock;

use Piwik\Tests\Framework\Mock\Tracker\Response;

class TrackerResponse extends Response
{
    private $invalidRequests = array();

    /**
     * @var bool
     */
    private $isAuthenticated = false;

    public function setInvalidRequests($invalidRequests)
    {
        $this->invalidRequests = $invalidRequests;
    }

    public function setIsAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
    }
}
