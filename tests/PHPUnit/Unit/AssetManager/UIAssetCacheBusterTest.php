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
use Piwik\Tests\Framework\TestCase\UnitTestCase;

class UIAssetCacheBusterTest extends UnitTestCase
{
    /**
     * @var UIAssetCacheBuster
     */
    private $cacheBuster;

    public function setUp()
    {
        parent::setUp();
        $this->cacheBuster = $this->environment->getContainer()->get('Piwik\AssetManager\UIAssetCacheBuster');
    }

    /**
     * @group Core
     */
    public function test_md5BasedCacheBuster()
    {
        $this->assertEquals('098f6bcd4621d373cade4e832627b4f6', $this->cacheBuster->md5BasedCacheBuster('test'));
    }
}
