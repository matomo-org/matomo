<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\Integration;

use Piwik\Exception\NoPrivilegesException;
use Piwik\Plugins\Ecommerce\Reports\GetItemsSku;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Ecommerce
 * @group ReportsTest
 * @group Plugins
 */
class ReportsTest extends IntegrationTestCase
{
    private array $get = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->get = $_GET;
    }

    public function tearDown(): void
    {
        $_GET = $this->get;

        parent::tearDown();
    }

    public function testCheckIsEnabledRefusesAReportForASiteWithoutEcommerce()
    {
        $idSite = Fixture::createWebsite('2024-01-01 00:00:00', $ecommerce = 0);
        $_GET['idSite'] = (string) $idSite;

        $this->expectException(NoPrivilegesException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('General_ExceptionReportNotEnabled');

        (new GetItemsSku())->checkIsEnabled();
    }
}
