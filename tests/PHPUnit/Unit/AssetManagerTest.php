<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit;

use Piwik\AssetManager;
use Piwik\Config;

/**
 * @group AssetManager
 */
class AssetManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testIsMergedAssetsDisabled()
    {
        $manager = new AssetManager();

        // default
        $this->assertFalse($manager->isMergedAssetsDisabled());

        // with configuration
        $defaultConfig = Config::getInstance()->Development['disable_merged_assets'];

        Config::getInstance()->Development['disable_merged_assets'] = false;
        $this->assertFalse($manager->isMergedAssetsDisabled());

        Config::getInstance()->Development['disable_merged_assets'] = true;
        $this->assertTrue($manager->isMergedAssetsDisabled());

        // reset the config
        Config::getInstance()->Development['disable_merged_assets'] = $defaultConfig;

        // with $_GET parameter
        $_GET['disable_merged_assets'] = '1';
        $this->assertTrue($manager->isMergedAssetsDisabled());
        unset($_GET['disable_merged_assets']);

        $_GET['disable_merged_assets'] = '0';
        $this->assertFalse($manager->isMergedAssetsDisabled());
        unset($_GET['disable_merged_assets']);
    }
}
