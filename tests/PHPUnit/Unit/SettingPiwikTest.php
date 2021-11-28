<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Config;
use Piwik\SettingsPiwik;

/**
 * @group Core
 * @group SettingsServer
 */
class SettingPiwikTest extends \PHPUnit\Framework\TestCase
{


    public function setUp(): void
    {
        parent::setUp();


    }

    public function test_passStringToIsUniqueVisitorsEnabled()
    {
        $result = SettingsPiwik::isUniqueVisitorsEnabled('day');
        $this->assertTrue($result);
    }

    public function test_passArrayToIsUniqueVisitorsEnabled()
    {
        $result = SettingsPiwik::isUniqueVisitorsEnabled(['test','array']);
        $this->assertFalse($result);

        $result = SettingsPiwik::isUniqueVisitorsEnabled(['day']);
        $this->assertTrue($result);
    }

}
