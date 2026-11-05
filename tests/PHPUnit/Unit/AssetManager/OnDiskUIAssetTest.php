<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\AssetManager\UIAsset {
    // OnDiskUIAsset calls fwrite() unqualified, so this override runs instead of the global one
    function fwrite($stream, string $data)
    {
        if (\Piwik\Tests\Unit\AssetManager\OnDiskUIAssetTest::$onWrite) {
            (\Piwik\Tests\Unit\AssetManager\OnDiskUIAssetTest::$onWrite)();
        }
        return \fwrite($stream, $data);
    }
}

namespace Piwik\Tests\Unit\AssetManager {
    use PHPUnit\Framework\TestCase;
    use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
    use Piwik\Filesystem;

    /**
     * @group Core
     */
    class OnDiskUIAssetTest extends TestCase
    {
        public static $onWrite;

        public function testWriteContentKeepsThePreviousFileReadableWhileWriting()
        {
            $dir = PIWIK_INCLUDE_PATH . '/tmp/OnDiskUIAssetTest';
            Filesystem::mkdir($dir);
            $asset = new OnDiskUIAsset($dir, 'merged.css');
            $asset->writeContent('old content');

            $seen = null;
            self::$onWrite = function () use ($asset, &$seen) {
                $seen = @file_get_contents($asset->getAbsoluteLocation());
            };
            try {
                $asset->writeContent('new content');
            } finally {
                self::$onWrite = null;
            }

            $this->assertSame('old content', $seen);
            $this->assertSame('new content', $asset->getContent());
            Filesystem::unlinkRecursive($dir, true);
        }
    }
}
