<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Filesystem;
use Piwik\Tests\Framework\Mock\File;

/**
 * @group Core
 * @group FileSystem
 */
class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    private $testPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->testPath = PIWIK_INCLUDE_PATH . '/tmp/filesystemtest';
        Filesystem::mkdir($this->testPath);
    }

    public function tearDown(): void
    {
        Filesystem::unlinkRecursive($this->testPath, true);

        parent::tearDown();
    }

    public function testSortFilesDescByPathLengthShouldNotFailIfEmptyArrayGiven()
    {
        $result = Filesystem::sortFilesDescByPathLength(array());
        $this->assertEquals(array(), $result);
    }

    public function testSortFilesDescByPathLengthShouldNotChangeOrderIfAllHaveSameLength()
    {
        $input  = array('xyz/1.gif', 'x/xyz.gif', 'xxyyzzgg');
        $result = Filesystem::sortFilesDescByPathLength($input);

        $input = array('x/xyz.gif', 'xyz/1.gif', 'xxyyzzgg');

        $this->assertEquals($input, $result);
    }

    public function testSortFilesDescByPathLengthShouldOrderDescIfDifferentLengthsGiven()
    {
        $input  = array('xyz/1.gif', '1.gif', 'x', 'x/xyz.gif', 'xyz', 'xxyyzzgg', 'xyz/long.gif');
        $result = Filesystem::sortFilesDescByPathLength($input);

        $expected = array(
            'xyz/long.gif',
            'x/xyz.gif',
            'xyz/1.gif',
            'xxyyzzgg',
            '1.gif',
            'xyz',
            'x',
        );
        $this->assertEquals($expected, $result);
    }

    public function testDirectoryDiffShouldNotReturnDifferenceIfBothDirectoriesAreSame()
    {
        $dir    = PIWIK_INCLUDE_PATH . '/core';
        $result = Filesystem::directoryDiff($dir, $dir);

        $this->assertEquals(array(), $result);
    }

    public function testDirectoryDiffShouldNotReturnAnythingIfTargetEmpty()
    {
        $result = Filesystem::directoryDiff($this->createSourceFiles(), $this->createEmptyTarget());

        $this->assertEquals(array(), $result);
    }

    public function testDirectoryDiffShouldReturnAllTargetFilesIfSourceIsEmpty()
    {
        $result = Filesystem::directoryDiff($this->createEmptySource(), $this->createTargetFiles());

        $this->assertEquals(array(
            '/DataTable',
            '/DataTable/BaseFilter.php',
            '/DataTable/Bridges.php',
            '/DataTable/DataTableInterface.php',
            '/DataTable/Filter',
            '/DataTable/Filter/index.htm', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Filter/index.php', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Manager.php',
            '/DataTable/Map.php',
            '/DataTable/Renderer',
            '/DataTable/Renderer.php',
            '/DataTable/Renderer/Console.php',
            '/DataTable/Renderer/Csv.php',
            '/DataTable/Renderer/Html.php',
            '/DataTable/Renderer/Json.php',
            '/DataTable/Renderer/Rss.php',
            '/DataTable/Renderer/Tsv.php',
            '/DataTable/Renderer/Xml',
            '/DataTable/Renderer/Xml.php',
            '/DataTable/Renderer/Xml/Other.php',
            '/DataTable/Renderer/Xml/index.htm',  // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Renderer/Xml/index.php',  // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Renderer/index.htm', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Renderer/index.php', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Row',
            '/DataTable/Row.php',
            '/DataTable/Row/DataTableSummaryRow.php',
            '/DataTable/Row/index.htm', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Row/index.php', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Simple.php',
            '/DataTable/index.htm', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/index.php', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing

        ), $result);
    }

    public function testDirectoryDiffShouldReturnFilesPresentInTargetButNotSourceIfSourceAndTargetGiven()
    {
        $result = Filesystem::directoryDiff($this->createSourceFiles(), $this->createTargetFiles());

        $this->assertEquals(array(
            '/DataTable/Filter',
            '/DataTable/Filter/index.htm', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Filter/index.php', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Renderer/Json.php',
            '/DataTable/Renderer/Rss.php',
            '/DataTable/Renderer/Xml',
            '/DataTable/Renderer/Xml/Other.php',
            '/DataTable/Renderer/Xml/index.htm', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Renderer/Xml/index.php', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Row',
            '/DataTable/Row/DataTableSummaryRow.php',
            '/DataTable/Row/index.htm', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
            '/DataTable/Row/index.php', // this was is created as side effect of "Target files" being within the tmp/ folder, @see createIndexFilesToPreventDirectoryListing
        ), $result);
    }

    public function testUnlinkTargetFilesNotPresentInSourceShouldUnlinkFilesPresentInTargetButNotSourceIfSourceAndTargetGiven()
    {
        $source = $this->createSourceFiles();
        $target = $this->createTargetFiles();

        // make sure there is a difference between those folders
        $result = Filesystem::directoryDiff($source, $target);
        $this->assertCount(13, $result);

        Filesystem::unlinkTargetFilesNotPresentInSource($source, $target);

        // make sure there is no longer a difference
        $result = Filesystem::directoryDiff($source, $target);
        $this->assertEquals(array(), $result);

        $result = Filesystem::directoryDiff($target, $source);
        $this->assertEquals(array(
             '/DataTable/NotInTarget.php',
             '/DataTable/Renderer/NotInTarget.php'
        ), $result);
    }

    public function testUnlinkTargetFilesNotPresentInSourceShouldNotFailIfBothEmpty()
    {
        self::expectNotToPerformAssertions();

        $source = $this->createEmptySource();
        $target = $this->createEmptyTarget();

        Filesystem::unlinkTargetFilesNotPresentInSource($source, $target);
    }

    public function testUnlinkTargetFilesNotPresentInSourceShouldUnlinkAllTargetFilesIfSourceIsEmpty()
    {
        $source = $this->createEmptySource();
        $target = $this->createTargetFiles();

        // make sure there is a difference between those folders
        $result = Filesystem::directoryDiff($source, $target);
        $this->assertNotEmpty($result);

        Filesystem::unlinkTargetFilesNotPresentInSource($source, $target);

        // make sure there is no longer a difference
        $result = Filesystem::directoryDiff($source, $target);
        $this->assertEquals([], $result);

        $result = Filesystem::directoryDiff($target, $source);
        $this->assertEquals(array(), $result);
    }

    public function testUnlockTargetFilesNotPresentInSourceDoNotAttemptToUnlinkFilesWithTheSameCaseInsensitiveName()
    {
        $sourceInsensitive = $this->createCaseInsensitiveSourceFiles();
        $targetInsensitive = $this->createCaseInsensitiveTargetFiles();

        // Target: /CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue'
        // Source: /CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue'

        $result = Filesystem::directoryDiff($sourceInsensitive, $targetInsensitive);

        if (Filesystem::isFileSystemCaseInsensitive()) {
            // Case insensitive filesystem:
            // Since the target and source will be treated as the same file then we do not want directoryDiff() to
            // report a difference as copying the source command will overwrite the target file. Reporting a difference
            // will cause the target file to be unlinked after the copy which will result in a missing file.

            $this->assertEquals(array(), $result);
        } else {
            // Case sensitive filesystem:
            // directoryDiff() should report a difference and we should be able to unlink the target file safely after
            // the source file has been copied.

            // make sure there is a difference between those folders
            $this->assertNotEmpty($result);

            Filesystem::unlinkTargetFilesNotPresentInSource($sourceInsensitive, $targetInsensitive);

            // make sure there is no longer a difference
            $result = Filesystem::directoryDiff($sourceInsensitive, $targetInsensitive);
            $this->assertEquals(array(), $result);

            $result = Filesystem::directoryDiff($targetInsensitive, $sourceInsensitive);
            $this->assertEquals(array(
                 '/CoreHome/vue/src/MenuItemsDropdown',
                 '/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue',
                 '/CoreHome/vue/src/MenuItemsDropdown/index.htm',
                 '/CoreHome/vue/src/MenuItemsDropdown/index.php',
            ), $result);
        }
    }

    private function createSourceFiles()
    {
        $source = $this->createEmptySource();
        Filesystem::mkdir($source . '/DataTable');
        Filesystem::mkdir($source . '/DataTable/Renderer');

        file_put_contents($source . '/DataTable/Renderer/Console.php', '');
        file_put_contents($source . '/DataTable/Renderer/Csv.php', '');
        file_put_contents($source . '/DataTable/Renderer/Html.php', '');
        file_put_contents($source . '/DataTable/Renderer/Tsv.php', '');
        file_put_contents($source . '/DataTable/Renderer/Xml.php', '');
        file_put_contents($source . '/DataTable/Renderer/NotInTarget.php', '');

        file_put_contents($source . '/DataTable/BaseFilter.php', '');
        file_put_contents($source . '/DataTable/Bridges.php', '');
        file_put_contents($source . '/DataTable/DataTableInterface.php', '');
        file_put_contents($source . '/DataTable/NotInTarget.php', '');
        file_put_contents($source . '/DataTable/Manager.php', '');
        file_put_contents($source . '/DataTable/Map.php', '');
        file_put_contents($source . '/DataTable/Renderer.php', '');
        file_put_contents($source . '/DataTable/Row.php', '');
        file_put_contents($source . '/DataTable/Simple.php', '');

        return $source;
    }

    private function createTargetFiles()
    {
        $target = $this->createEmptyTarget();
        Filesystem::mkdir($target . '/DataTable');
        Filesystem::mkdir($target . '/DataTable/Filter');
        Filesystem::mkdir($target . '/DataTable/Renderer');
        Filesystem::mkdir($target . '/DataTable/Renderer/Xml');
        Filesystem::mkdir($target . '/DataTable/Row');

        file_put_contents($target . '/DataTable/Renderer/Console.php', '');
        file_put_contents($target . '/DataTable/Renderer/Csv.php', '');
        file_put_contents($target . '/DataTable/Renderer/Html.php', '');
        file_put_contents($target . '/DataTable/Renderer/Json.php', '');
        file_put_contents($target . '/DataTable/Renderer/Rss.php', '');
        file_put_contents($target . '/DataTable/Renderer/Tsv.php', '');
        file_put_contents($target . '/DataTable/Renderer/Xml.php', '');
        file_put_contents($target . '/DataTable/Renderer/Xml/Other.php', '');

        file_put_contents($target . '/DataTable/Row/DataTableSummaryRow.php', '');

        file_put_contents($target . '/DataTable/BaseFilter.php', '');
        file_put_contents($target . '/DataTable/Bridges.php', '');
        file_put_contents($target . '/DataTable/DataTableInterface.php', '');
        file_put_contents($target . '/DataTable/Manager.php', '');
        file_put_contents($target . '/DataTable/Map.php', '');
        file_put_contents($target . '/DataTable/Renderer.php', '');
        file_put_contents($target . '/DataTable/Row.php', '');
        file_put_contents($target . '/DataTable/Simple.php', '');

        return $target;
    }

    private function createEmptySource()
    {
        Filesystem::mkdir($this->testPath . '/source');

        return $this->testPath . '/source';
    }

    private function createEmptyTarget()
    {
        Filesystem::mkdir($this->testPath . '/target');

        return $this->testPath . '/target';
    }

    private function createCaseInsensitiveTargetFiles()
    {
        $target = $this->createEmptyTarget();
        Filesystem::mkdir($target . '/CoreHome/vue/src/Menuitemsdropdown');

        file_put_contents($target . '/CoreHome/vue/src/Menuitemsdropdown/Menuitemsdropdown.vue', '');

        return $target;
    }

    private function createCaseInsensitiveSourceFiles()
    {
        $source = $this->createEmptySource();
        Filesystem::mkdir($source . '/CoreHome/vue/src/MenuItemsDropdown');

        file_put_contents($source . '/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue', '');

        return $source;
    }

    public function testGetFileSizeZeroSize()
    {
        File::setFileSize(0);

        $size = Filesystem::getFileSize(__FILE__);
        $this->assertEquals(0, $size);

        $size = Filesystem::getFileSize(__FILE__, 'KB');
        $this->assertEquals(0, $size);

        $size = Filesystem::getFileSize(__FILE__, 'MB');
        $this->assertEquals(0, $size);

        $size = Filesystem::getFileSize(__FILE__, 'GB');
        $this->assertEquals(0, $size);

        $size = Filesystem::getFileSize(__FILE__, 'TB');
        $this->assertEquals(0, $size);
    }

    public function testGetFileSizeLowSize()
    {
        File::setFileSize(1024);

        $size = Filesystem::getFileSize(__FILE__);
        $this->assertEquals(1024, $size);

        $size = Filesystem::getFileSize(__FILE__, 'KB');
        $this->assertEquals(1, $size);

        $size = Filesystem::getFileSize(__FILE__, 'MB');
        $this->assertGreaterThanOrEqual(0.0009, $size);
        $this->assertLessThanOrEqual(0.0011, $size);

        $size = Filesystem::getFileSize(__FILE__, 'GB');
        $this->assertGreaterThanOrEqual(0.0000009, $size);
        $this->assertLessThanOrEqual(0.0000011, $size);

        $size = Filesystem::getFileSize(__FILE__, 'TB');
        $this->assertGreaterThanOrEqual(0.0000000009, $size);
        $this->assertLessThanOrEqual(0.0000000011, $size);
    }

    public function testGetFileSizeHighSize()
    {
        File::setFileSize(1073741824);

        $size = Filesystem::getFileSize(__FILE__, 'B');
        $this->assertEquals(1073741824, $size);

        $size = Filesystem::getFileSize(__FILE__, 'KB');
        $this->assertEquals(1048576, $size);

        $size = Filesystem::getFileSize(__FILE__, 'MB');
        $this->assertEquals(1024, $size);

        $size = Filesystem::getFileSize(__FILE__, 'GB');
        $this->assertEquals(1, $size);

        $size = Filesystem::getFileSize(__FILE__, 'TB');
        $this->assertGreaterThanOrEqual(0.0009, $size);
        $this->assertLessThanOrEqual(0.0011, $size);
    }

    public function testGetFileSizeShouldRecognizeLowerUnits()
    {
        File::setFileSize(1073741824);

        $size = Filesystem::getFileSize(__FILE__, 'b');
        $this->assertEquals(1073741824, $size);

        $size = Filesystem::getFileSize(__FILE__, 'kb');
        $this->assertEquals(1048576, $size);

        $size = Filesystem::getFileSize(__FILE__, 'mB');
        $this->assertEquals(1024, $size);

        $size = Filesystem::getFileSize(__FILE__, 'Gb');
        $this->assertEquals(1, $size);
    }

    public function testGetFileSizeShouldThrowExceptionIfInvalidUnit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid unit given');

        Filesystem::getFileSize(__FILE__, 'iV');
    }

    public function testGetFileSizeShouldReturnNullIfFileDoesNotExists()
    {
        File::setFileExists(false);
        $size = Filesystem::getFileSize(__FILE__);

        $this->assertNull($size);
    }

    /**
     * @dataProvider getSanitizeFilenameTestData
     */
    public function testSanitizeFilename(string $filename, string $expected): void
    {
        $this->assertSame(
            $expected,
            Filesystem::sanitizeFilename($filename)
        );
    }

    public function getSanitizeFilenameTestData(): array
    {
        return [
            ['reserved<>:"/\\|?*characters', 'reservedcharacters'],
            ["control\x00\x09\x0A\x7Fcharacters", 'controlcharacters'],
            ['  spaces are trimmed  ', 'spaces are trimmed'],
            ['unicode    spaces', 'unicode    spaces'],
            ['unicode‒–—dashes', 'unicode---dashes'],
            [
                // english (en) export for date range, replaced "thsp" + "endash"
                'Export _ Main metrics _ December 31, 2024 – January 1, 2025.csv',
                'Export _ Main metrics _ December 31, 2024 - January 1, 2025.csv',
            ],
            [
                // bulgarian (bg) export for date range, replaced "nnbsp" + "endash"
                'Запазване _ Главни метрики _ 31 декември 2024 г. – 1 януари 2025 г..csv',
                'Запазване _ Главни метрики _ 31 декември 2024 г. - 1 януари 2025 г..csv',
            ],
            [
                // basque (eu) export for date range, replaced "endash"
                'Esportatu _ Metrika nagusiak _ 2025(e)ko urtarrila 1–2.csv',
                'Esportatu _ Metrika nagusiak _ 2025(e)ko urtarrila 1-2.csv',
            ],
            [
                // kurdish (ku) export for date range, replaced "thsp" + "endash"
                'Export _ Main metrics _ 31ê berfanbara 2024an – 1ê rêbendana 2025an.csv',
                'Export _ Main metrics _ 31ê berfanbara 2024an - 1ê rêbendana 2025an.csv',
            ],
            [
                // japanese (ja) export for date range, unchanged
                'エクスポート _ メインメトリクス _ 2024年12月31日～2025年01月1日.csv',
                'エクスポート _ メインメトリクス _ 2024年12月31日～2025年01月1日.csv',
            ],
            [
                // simplified chinese (zh-cn) export for date range, unchanged
                '导出 _ 主要指标 _ 2025年01月1日至2日.csv',
                '导出 _ 主要指标 _ 2025年01月1日至2日.csv',
            ],
        ];
    }
}
