<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use PHPUnit_Framework_TestCase;
use Piwik\AssetManager\UIAssetCacheBuster;

class UIAssetCacheBusterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var UIAssetCacheBuster
     */
    private $cacheBuster;

    public function setUp()
    {
        $this->cacheBuster = UIAssetCacheBuster::getInstance();
    }

    /**
     * @group Core
     */
    public function test_md5BasedCacheBuster()
    {
        $this->assertEquals('098f6bcd4621d373cade4e832627b4f6', $this->cacheBuster->md5BasedCacheBuster('test'));
    }
}
