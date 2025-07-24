<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\UsersManager\tests\Fixtures\ManyUsers;

/**
 * Fixture with a larger number of users setting the 'now' date to 2013
 */
class ManyUsersPastDate extends ManyUsers
{
    public $dateTime = '2013-01-23 01:23:45';

    public function provideContainerConfig()
    {
        Date::$now = strtotime($this->dateTime);

        return parent::provideContainerConfig();
    }
}
