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

        /**
         * @var string
         */
        private $directory;

        public function setUp(): void
        {
            parent::setUp();

            $this->directory = PIWIK_INCLUDE_PATH . '/tmp/OnDiskUIAssetTest_' . uniqid();
            Filesystem::mkdir($this->directory);
        }

        public function tearDown(): void
        {
            self::$onWrite = null;
            Filesystem::unlinkRecursive($this->directory, true);

            parent::tearDown();
        }

        public function testWriteContentKeepsThePreviousFileReadableWhileWriting()
        {
            $asset = new OnDiskUIAsset($this->directory, 'merged.css');
            $asset->writeContent('old content');

            $hookCalled = false;
            $seen = null;
            self::$onWrite = function () use ($asset, &$hookCalled, &$seen) {
                $hookCalled = true;
                $seen = @file_get_contents($asset->getAbsoluteLocation());
            };

            $asset->writeContent('new content');

            $this->assertTrue($hookCalled, 'writeContent() no longer calls fwrite(), update this test');
            $this->assertSame('old content', $seen);
            $this->assertSame('new content', $asset->getContent());
            $this->assertSame([], glob($this->directory . '/*.tmp'));
        }
    }
}
