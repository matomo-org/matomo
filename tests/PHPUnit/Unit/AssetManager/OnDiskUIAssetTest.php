<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use PHPUnit\Framework\TestCase;
use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\Filesystem;

/**
 * @group Core
 */
class OnDiskUIAssetTest extends TestCase
{
    /**
     * @var string
     */
    private $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir() . '/OnDiskUIAssetTest_' . uniqid();
        Filesystem::mkdir($this->directory);
    }

    public function tearDown(): void
    {
        Filesystem::unlinkRecursive($this->directory, true);

        parent::tearDown();
    }

    public function testWriteContentReplacesTheExistingFile()
    {
        $asset = new OnDiskUIAsset($this->directory, 'merged.css');

        $asset->writeContent('old content');
        $asset->writeContent('new content');

        $this->assertSame('new content', $asset->getContent());
        $this->assertSame(['merged.css'], $this->getFilesInDirectory());
    }

    public function testWriteContentRemovesCompressedVersionsOfThePreviousContent()
    {
        $asset = new OnDiskUIAsset($this->directory, 'merged.css');
        $asset->writeContent('old content');
        file_put_contents($asset->getAbsoluteLocation() . '.gz', 'old compressed content');
        file_put_contents($asset->getAbsoluteLocation() . '.deflate', 'old compressed content');

        $asset->writeContent('new content');

        $this->assertSame(['merged.css'], $this->getFilesInDirectory());
    }

    private function getFilesInDirectory(): array
    {
        return array_values(array_diff(scandir($this->directory), ['.', '..']));
    }
}
