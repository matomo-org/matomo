<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Fixtures;

use Piwik\DbHelper;
use Piwik\Plugins\UsersManager\API;
use Piwik\Tests\Framework\Fixture;

class AnonymousUser extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite($this->dateTime);

        DbHelper::createAnonymousUser();

        $api = API::getInstance();
        $api->addUser('regularUser', 'password', 'regularUser@matomo.org');
    }
}
