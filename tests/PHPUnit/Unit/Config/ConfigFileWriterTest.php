<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Piwik\Config\ConfigFileWriter;

/**
 * @group Core
 * @group Config
 */
class ConfigFileWriterTest extends TestCase
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $target;

    public function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/matomo-config-writer-' . uniqid();
        mkdir($this->dir . '/config', 0777, true);
        $this->target = $this->dir . '/config/config.ini.php';
    }

    public function tearDown(): void
    {
        // Restore permissions first, or the recursive delete cannot descend.
        @chmod($this->dir . '/config', 0777);
        if (file_exists($this->target)) {
            @chmod($this->target, 0666);
        }

        foreach ((array) @glob($this->dir . '/config/{,.}*', GLOB_BRACE) as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($this->dir . '/config');
        @rmdir($this->dir);

        parent::tearDown();
    }

    public function testWritesContentsAndReplacesAtomicallyForAPlainFile()
    {
        file_put_contents($this->target, 'old');

        $result = ConfigFileWriter::write($this->target, 'new contents');

        $this->assertSame(ConfigFileWriter::OK, $result);
        $this->assertSame('new contents', file_get_contents($this->target));
        $this->assertSame(ConfigFileWriter::MODE_ATOMIC, ConfigFileWriter::getLastMode());
        $this->assertNull(ConfigFileWriter::getLastBlocker());
    }

    public function testUsesAtomicPathWhenTheFileDoesNotExistYet()
    {
        // The first write of every installation. A naive realpath() of a missing file
        // returns false and would silently disable the atomic path here.
        $this->assertNull(ConfigFileWriter::inspect($this->target));

        $result = ConfigFileWriter::write($this->target, 'created');

        $this->assertSame(ConfigFileWriter::OK, $result);
        $this->assertSame('created', file_get_contents($this->target));
        $this->assertSame(ConfigFileWriter::MODE_ATOMIC, ConfigFileWriter::getLastMode());
    }

    public function testCreatesAMissingFileWithTheModeTheFilesystemWouldHaveApplied()
    {
        $reference = $this->dir . '/config/reference';
        touch($reference);
        $expected = fileperms($reference) & 07777;

        ConfigFileWriter::write($this->target, 'created');

        clearstatcache(true, $this->target);
        $this->assertSame($expected, fileperms($this->target) & 07777);
    }

    public function testPreservesTheModeOfAnExistingFile()
    {
        file_put_contents($this->target, 'old');
        chmod($this->target, 0600);

        ConfigFileWriter::write($this->target, 'new');

        clearstatcache(true, $this->target);
        $this->assertSame(0600, fileperms($this->target) & 07777);
        $this->assertSame(ConfigFileWriter::MODE_ATOMIC, ConfigFileWriter::getLastMode());
    }

    public function testReplacesTheTargetOfASymlinkAndLeavesTheLinkInPlace()
    {
        $real = $this->dir . '/real-config.ini.php';
        file_put_contents($real, 'old');
        symlink($real, $this->target);

        $result = ConfigFileWriter::write($this->target, 'new');

        $this->assertSame(ConfigFileWriter::OK, $result);
        $this->assertTrue(is_link($this->target), 'the symlink must survive the write');
        $this->assertSame('new', file_get_contents($real));

        @unlink($this->target);
        @unlink($real);
    }

    public function testFallsBackForADanglingSymlink()
    {
        // Replacing the link with a regular file would silently discard where the
        // operator wanted the config to live.
        symlink($this->dir . '/does-not-exist.ini.php', $this->target);

        $this->assertSame(ConfigFileWriter::BLOCKED_UNRESOLVABLE, ConfigFileWriter::inspect($this->target));

        @unlink($this->target);
    }

    public function testFallsBackWhenTheDirectoryIsNotWritable()
    {
        file_put_contents($this->target, 'old');
        chmod($this->dir . '/config', 0555);
        $this->skipUnlessPermissionsApply($this->dir . '/config');

        $this->assertSame(ConfigFileWriter::BLOCKED_DIR_NOT_WRITABLE, ConfigFileWriter::inspect($this->target));

        $result = ConfigFileWriter::write($this->target, 'new');

        $this->assertSame(ConfigFileWriter::OK, $result);
        $this->assertSame('new', file_get_contents($this->target));
        $this->assertSame(ConfigFileWriter::MODE_IN_PLACE, ConfigFileWriter::getLastMode());
        $this->assertSame(ConfigFileWriter::BLOCKED_DIR_NOT_WRITABLE, ConfigFileWriter::getLastBlocker());
    }

    public function testFallsBackWhenTheFileItselfIsNotWritable()
    {
        // Otherwise the directory's permission silently becomes the gate and making the
        // config read-only stops working.
        file_put_contents($this->target, 'old');
        chmod($this->target, 0444);
        $this->skipUnlessPermissionsApply($this->target);

        $this->assertSame(ConfigFileWriter::BLOCKED_FILE_NOT_WRITABLE, ConfigFileWriter::inspect($this->target));

        $result = ConfigFileWriter::write($this->target, 'new');

        $this->assertSame(ConfigFileWriter::FAILED, $result);
        $this->assertSame('old', file_get_contents($this->target));
    }

    public function testFallsBackWhenTheFileHasMoreThanOneHardLink()
    {
        file_put_contents($this->target, 'old');
        $other = $this->dir . '/config/other.config.ini.php';

        if (!@link($this->target, $other)) {
            $this->markTestSkipped('filesystem does not support hard links');
        }

        $this->assertSame(ConfigFileWriter::BLOCKED_HARD_LINKED, ConfigFileWriter::inspect($this->target));

        ConfigFileWriter::write($this->target, 'new');

        // Written in place, so both names still see the same contents.
        $this->assertSame('new', file_get_contents($this->target));
        $this->assertSame('new', file_get_contents($other));
    }

    public function testFallsBackWhenAtomicWritesAreDisabled()
    {
        file_put_contents($this->target, 'old');

        $this->assertSame(ConfigFileWriter::BLOCKED_DISABLED, ConfigFileWriter::inspect($this->target, false));

        $result = ConfigFileWriter::write($this->target, 'new', false);

        $this->assertSame(ConfigFileWriter::OK, $result);
        $this->assertSame('new', file_get_contents($this->target));
        $this->assertSame(ConfigFileWriter::MODE_IN_PLACE, ConfigFileWriter::getLastMode());
        $this->assertSame(ConfigFileWriter::BLOCKED_DISABLED, ConfigFileWriter::getLastBlocker());
    }

    public function testLeavesNoTemporaryFileBehindAfterASuccessfulWrite()
    {
        ConfigFileWriter::write($this->target, 'created');

        $this->assertSame([], $this->findTempFiles());
    }

    public function testSweepsAStaleTemporaryFileButKeepsARecentOne()
    {
        file_put_contents($this->target, 'old');

        $stale = $this->dir . '/config/.config.ini.php.new-stale.php';
        $fresh = $this->dir . '/config/.config.ini.php.new-fresh.php';
        file_put_contents($stale, 'leftover');
        file_put_contents($fresh, 'in flight');
        touch($stale, time() - 7200);

        ConfigFileWriter::sweepStaleTemps($this->target);

        $this->assertFileDoesNotExist($stale);
        $this->assertFileExists($fresh);

        @unlink($fresh);
    }

    public function testSweepsLeftoversForAMissingTargetToo()
    {
        // The path resolution has to survive the file not existing, or the sweep looks
        // in the wrong directory.
        $stale = $this->dir . '/config/.config.ini.php.new-stale.php';
        file_put_contents($stale, 'leftover');
        touch($stale, time() - 7200);

        ConfigFileWriter::sweepStaleTemps($this->target);

        $this->assertFileDoesNotExist($stale);
    }

    public function testDoesNotSweepTemporaryFilesOfADifferentConfigFile()
    {
        $otherTemp = $this->dir . '/config/.other.config.ini.php.new-stale.php';
        file_put_contents($otherTemp, 'not mine');
        touch($otherTemp, time() - 7200);

        ConfigFileWriter::sweepStaleTemps($this->target);

        $this->assertFileExists($otherTemp);

        @unlink($otherTemp);
    }

    /**
     * The generated name must stay in step with the fileintegrity.ignore patterns in
     * config/global.php.
     */
    public function testTemporaryNameMatchesTheFileIntegrityIgnorePatterns()
    {
        $patterns = [
            'config/*.config.ini.php.new-*.php',
            'misc/user/*/.config.ini.php.new-*.php',
        ];

        $globalConfig = file_get_contents(__DIR__ . '/../../../../config/global.php');

        foreach ($patterns as $pattern) {
            $this->assertStringContainsString(
                "'" . $pattern . "'",
                $globalConfig,
                'fileintegrity.ignore must list ' . $pattern
            );
        }

        // The three shapes Config::getLocalConfigInfoForHostname() can produce.
        $names = [
            'config/.config.ini.php.new-6810e4a1f2b34.php',
            'config/.example.org.config.ini.php.new-6810e4a1f2b34.php',
            'misc/user/example.org/.config.ini.php.new-6810e4a1f2b34.php',
        ];

        foreach ($names as $name) {
            $matched = false;
            foreach ($patterns as $pattern) {
                if (fnmatch($pattern, $name, defined('FNM_CASEFOLD') ? FNM_CASEFOLD : 0)) {
                    $matched = true;
                    break;
                }
            }
            $this->assertTrue($matched, "no fileintegrity.ignore pattern matches $name");
        }

        // The config file itself must keep being checked.
        $this->assertFalse(
            fnmatch($patterns[0], 'config/config.ini.php'),
            'the pattern must not swallow the config file'
        );
    }

    /**
     * access(W_OK) succeeds for uid 0 whatever the mode says. Checked against the path
     * rather than the uid, so it needs no posix extension.
     */
    private function skipUnlessPermissionsApply(string $path): void
    {
        if (is_writable($path)) {
            $this->markTestSkipped('file permissions do not constrain this user');
        }
    }

    /**
     * @return string[]
     */
    private function findTempFiles(): array
    {
        return array_values((array) glob($this->dir . '/config/.config.ini.php.new-*.php'));
    }
}
