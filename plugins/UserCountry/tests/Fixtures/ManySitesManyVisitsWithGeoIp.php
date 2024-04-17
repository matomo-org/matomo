<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Fixtures;

use Piwik\Tests\Fixtures\ManyVisitsWithGeoIP;

class ManySitesManyVisitsWithGeoIp extends ManyVisitsWithGeoIP
{
    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = 2;
        $this->ips = array_reverse($this->ips);
        $this->userAgents = array_reverse($this->userAgents);

        parent::setUp();
    }
}
