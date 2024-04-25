<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use PHPUnit\Framework\TestCase;
use Piwik\AssetManager\UIAssetCacheBuster;

class UIAssetCacheBusterTest extends TestCase
{
    /**
     * @var UIAssetCacheBuster
     */
    private $cacheBuster;

    public function setUp(): void
    {
        $this->cacheBuster = UIAssetCacheBuster::getInstance();
    }

    /**
     * @group Core
     */
    public function testMd5BasedCacheBuster()
    {
        $this->assertEquals('098f6bcd4621d373cade4e832627b4f6', $this->cacheBuster->md5BasedCacheBuster('test'));
    }
}
