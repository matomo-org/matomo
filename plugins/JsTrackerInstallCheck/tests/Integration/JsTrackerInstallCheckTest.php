<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Plugins\JsTrackerInstallCheck\JsTrackerInstallCheck;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group JsTrackerInstallCheck
 * @group Plugins
 * @group JsTrackerInstallCheckTest
 */
class JsTrackerInstallCheckTest extends IntegrationTestCase
{
    /**
     * @var JsTrackerInstallCheck
     */
    protected $jsTrackerInstallCheck;

    /**
     * @var int
     */
    private $idSite1;

    /**
     * @var int
     */
    private $idSite2;

    public function setUp(): void
    {
        parent::setUp();
        $this->jsTrackerInstallCheck = new JsTrackerInstallCheck();

        $this->idSite1 = Fixture::createWebsite('2014-01-01 00:00:00');
        $this->idSite2 = Fixture::createWebsite('2014-01-01 00:00:00');
    }
}
