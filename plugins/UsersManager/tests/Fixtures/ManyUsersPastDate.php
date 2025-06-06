<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Fixtures;

use Piwik\Plugins\UsersManager\tests\Fixtures\ManyUsers;

/**
 * Fixture with a larger number of users setting the 'now' date to 2013
 */
class ManyUsersPastDate extends ManyUsers
{
    public function provideContainerConfig()
    {
        return [
            'Tests.now' => \Piwik\DI::decorate(function () {
                return strtotime($this->dateTime);
            }),
        ];
    }
}
