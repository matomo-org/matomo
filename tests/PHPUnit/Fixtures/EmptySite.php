<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that adds one site with no visits
 */
class EmptySite extends Fixture
{
    public $idSite = 1;
    public function setUp(): void
    {
        Fixture::createSuperUser();
        $this->setUpWebsites();
    }
    public function tearDown(): void
    {
        // empty
    }
    private function setUpWebsites()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite('2021-01-01');
        }
    }
}
