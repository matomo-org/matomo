<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Config;

/**
 * Writes the INI config file, replacing it atomically where the filesystem allows it.
 *
 * Readers of `config.ini.php` take no lock, so an in-place write can be seen truncated.
 * A rename cannot: it is a single metadata operation. Where a rename is not possible,
 * {@see atomicBlocker()} falls back to the in-place write Matomo used before.
 *
 * Has no dependencies on purpose: it runs while the config is being written, so it must
 * not read config or reach into the container.
 */
class ConfigFileWriter
{
    /** Contents are on disk at the target path. */
    public const OK = 0;

    /**
     * Not written. On the in-place path the target may have been truncated: a short
     * write and a failure to open are both `false` from file_put_contents().
     */
    public const FAILED = 1;

    /**
     * Not written, and the target still holds the previous contents. Do not retry in
     * place: that would truncate them and fail the same way.
     */
    public const FAILED_TARGET_UNCHANGED = 2;

    /**
     * Atomic replace not possible here; fall back. Internal, never returned.
     */
    private const ATOMIC_UNAVAILABLE = 3;

    public const MODE_ATOMIC = 'atomic';
    public const MODE_IN_PLACE = 'in-place';

    public const BLOCKED_DISABLED = 'disabled';
    public const BLOCKED_MISSING_FUNCTION = 'missing_function';
    public const BLOCKED_UNRESOLVABLE = 'unresolvable';
    public const BLOCKED_DIR_NOT_WRITABLE = 'dir_not_writable';
    public const BLOCKED_FILE_NOT_WRITABLE = 'file_not_writable';
    public const BLOCKED_HARD_LINKED = 'hard_linked';
    public const BLOCKED_OWNER_MISMATCH = 'owner_mismatch';
    public const BLOCKED_REPLACE_FAILED = 'replace_failed';

    /**
     * Age at which a temporary file counts as left over. Sweeping a live one only
     * degrades that write to the in-place path, so it need not be exact.
     */
    private const TEMP_MAX_AGE = 3600;

    /** @var string|null */
    private static $lastMode = null;

    /** @var string|null */
    private static $lastBlocker = null;

    /**
     * Writes $contents to $path, atomically where possible.
     *
     * @param string $path Path to the config file. May be a symlink.
     * @param string $contents Complete file contents.
     * @param bool $allowAtomic Whether the atomic path may be used at all.
     * @return int One of the result constants.
     */
    public static function write(string $path, string $contents, bool $allowAtomic = true): int
    {
        self::sweepStaleTemps($path);

        $target = self::resolveTarget($path);
        $blocker = self::atomicBlocker($target, $allowAtomic);

        if ($blocker === null) {
            $result = self::writeAtomically($target, $contents);

            if ($result !== self::ATOMIC_UNAVAILABLE) {
                // Only a completed rename is an atomic write; a failed one replaced nothing.
                if ($result === self::OK) {
                    self::$lastMode = self::MODE_ATOMIC;
                    self::$lastBlocker = null;
                }

                return $result;
            }

            // The pre-check cannot see a bind mount, an immutable attribute or a
            // Windows handle held by a reader.
            $blocker = self::BLOCKED_REPLACE_FAILED;
        }

        self::$lastMode = self::MODE_IN_PLACE;
        self::$lastBlocker = $blocker;

        // As before, and against $path so a symlinked config keeps being followed.
        $written = @file_put_contents($path, $contents, LOCK_EX);

        return $written === strlen($contents) ? self::OK : self::FAILED;
    }

    /**
     * Returns the condition preventing an atomic replace of $path, or null if there is
     * none. Cheap enough to call on a diagnostics page; it performs no write.
     *
     * @return string|null One of the BLOCKED_* constants.
     */
    public static function inspect(string $path, bool $allowAtomic = true): ?string
    {
        return self::atomicBlocker(self::resolveTarget($path), $allowAtomic);
    }

    /**
     * Returns the mode the most recent write in this process actually used, or null if
     * nothing has been written yet.
     *
     * @return string|null MODE_ATOMIC or MODE_IN_PLACE.
     */
    public static function getLastMode(): ?string
    {
        return self::$lastMode;
    }

    /**
     * Returns why the most recent write fell back to the in-place path, or null if it
     * did not.
     *
     * @return string|null One of the BLOCKED_* constants.
     */
    public static function getLastBlocker(): ?string
    {
        return self::$lastBlocker;
    }

    /**
     * Removes temporary files left by an interrupted write. Nothing else does, and the
     * file integrity check ignores the pattern only in the two default config locations.
     */
    public static function sweepStaleTemps(string $path): void
    {
        self::sweep($path, self::TEMP_MAX_AGE);
    }

    /**
     * Removes every temporary file for $path regardless of age, for when the config
     * itself is deleted: a temporary file holds the same credentials.
     */
    public static function sweepTemps(string $path): void
    {
        self::sweep($path, null);
    }

    /**
     * @param int|null $minimumAge Only unlink files older than this, or null for all of them.
     */
    private static function sweep(string $path, ?int $minimumAge): void
    {
        $target = self::resolveTarget($path);
        $base = $target !== false ? $target : $path;

        foreach ((array) @glob(self::tempPattern($base)) as $temp) {
            if ($minimumAge === null) {
                @unlink($temp);
                continue;
            }

            $mtime = @filemtime($temp);

            if ($mtime === false || (time() - $mtime) > $minimumAge) {
                @unlink($temp);
            }
        }
    }

    /**
     * Resolves $path to the file a rename would replace, so a symlinked config has its
     * target replaced and the temporary file lands on the target's filesystem.
     *
     * @return string|false False when no rename target can be determined.
     */
    private static function resolveTarget(string $path)
    {
        $target = realpath($path);

        if ($target !== false) {
            return $target;
        }

        // Dangling symlink: a rename would replace the link with a regular file.
        if (is_link($path)) {
            return false;
        }

        // Not created yet, as on every first write. Resolve the directory instead.
        $dir = realpath(dirname($path));

        return $dir === false ? false : $dir . DIRECTORY_SEPARATOR . basename($path);
    }

    /**
     * @param string|false $target Resolved target, as returned by resolveTarget().
     * @return string|null One of the BLOCKED_* constants, or null when a replace is possible.
     */
    private static function atomicBlocker($target, bool $allowAtomic): ?string
    {
        if (!$allowAtomic) {
            return self::BLOCKED_DISABLED;
        }

        // The mode has to survive the write, so a chmod that cannot run stops it.
        if (!function_exists('chmod')) {
            return self::BLOCKED_MISSING_FUNCTION;
        }

        if ($target === false) {
            return self::BLOCKED_UNRESOLVABLE;
        }

        // A rename needs the directory writable, not the file.
        if (!is_writable(dirname($target))) {
            return self::BLOCKED_DIR_NOT_WRITABLE;
        }

        if (!file_exists($target)) {
            return null;
        }

        // Honour the file's own mode, so a read-only config stays read-only and
        // Config::isFileWritable() stays the single answer.
        if (!is_writable($target)) {
            return self::BLOCKED_FILE_NOT_WRITABLE;
        }

        // A rename breaks hard links: the other name would keep the old contents.
        $stat = @stat($target);
        if (is_array($stat) && $stat['nlink'] > 1) {
            return self::BLOCKED_HARD_LINKED;
        }

        // A rename gives the file to the writing user, so a config owned by someone else
        // is written in place. Root included: chown() would act on a swappable name.
        // Skipped without posix; restoreIdentity() compares owners either way.
        if (function_exists('posix_geteuid')) {
            $owner = @fileowner($target);

            if ($owner !== false && $owner !== posix_geteuid()) {
                return self::BLOCKED_OWNER_MISMATCH;
            }
        }

        return null;
    }

    private static function writeAtomically(string $target, string $contents): int
    {
        $temp = self::tempPath($target);

        // 'x' fails if the name exists, so a planted symlink cannot redirect the write.
        $handle = @fopen($temp, 'xb');

        if ($handle === false) {
            // Out of space must not reach the in-place path, which would truncate the
            // real file and fail the same way. Inode exhaustion and quota are invisible
            // here and fall back, where truncating first usually still succeeds.
            $free = function_exists('disk_free_space') ? @disk_free_space(dirname($target)) : false;

            return ($free !== false && $free < strlen($contents))
                ? self::FAILED_TARGET_UNCHANGED
                : self::ATOMIC_UNAVAILABLE;
        }

        // The mode the filesystem applied. Reconstructing it from the umask is wrong
        // when the directory carries a default ACL.
        $stat = @fstat($handle);
        $createdMode = is_array($stat) ? ($stat['mode'] & 07777) : null;

        // Narrow before the credentials reach disk: it was created 0666 & ~umask.
        if (!@chmod($temp, 0600)) {
            fclose($handle);
            @unlink($temp);
            return self::ATOMIC_UNAVAILABLE;
        }

        $success = @fwrite($handle, $contents) === strlen($contents) && @fflush($handle);

        // Advisory: fsync() reports failure on some FUSE backends with the bytes fine.
        if ($success && function_exists('fsync')) {
            @fsync($handle);
        }

        // On NFS a write error commonly surfaces at close rather than at fwrite, so the
        // close has to be part of the verdict. Evaluated first so the handle always
        // closes.
        $success = @fclose($handle) && $success;

        if (!$success) {
            @unlink($temp);
            return self::FAILED_TARGET_UNCHANGED;
        }

        if (!self::restoreIdentity($temp, $target, $createdMode) || !@rename($temp, $target)) {
            @unlink($temp);
            return self::ATOMIC_UNAVAILABLE;
        }

        return self::OK;
    }

    /**
     * Gives the temporary file the identity of the file it is about to replace, before
     * the rename, so it is never visible at the target path with the wrong mode.
     *
     * @param int|null $createdMode Mode the filesystem applied when creating the temporary file.
     * @return bool False when the identity could not be preserved.
     */
    private static function restoreIdentity(string $temp, string $target, ?int $createdMode): bool
    {
        $mode = @fileperms($target);

        // No target to match: restore the mode the filesystem gave us before the
        // narrowing, or keep the narrow one, since erring open publishes credentials.
        if ($mode === false) {
            return @chmod($temp, $createdMode ?? 0600);
        }

        // Group before mode: widening first would expose the finished config to the
        // group it inherited on creation.
        $group = @filegroup($target);

        if ($group !== false && @filegroup($temp) !== $group) {
            if (!function_exists('chgrp') || !@chgrp($temp, $group)) {
                return false;
            }
        }

        // Owner is checked, never corrected: chown() would act on a name after the
        // handle closed. A mismatch means giving up the atomic path.
        $owner = @fileowner($target);

        if ($owner !== false && @fileowner($temp) !== $owner) {
            return false;
        }

        return @chmod($temp, $mode & 07777);
    }

    private static function tempPath(string $target): string
    {
        // uniqid() because random_bytes() and random_int() throw with no CSPRNG, and
        // no caller catches that. The exclusive create is what makes the name safe.
        $unique = str_replace('.', '', uniqid('', true));

        return self::tempPrefix($target) . $unique . '.php';
    }

    private static function tempPattern(string $target): string
    {
        return self::tempPrefix($target) . '*.php';
    }

    /**
     * Hidden, and ends in .php so a leaked temporary file is still handled by PHP and
     * neutralised by the exit header the config file starts with.
     */
    private static function tempPrefix(string $target): string
    {
        return dirname($target) . DIRECTORY_SEPARATOR . '.' . basename($target) . '.new-';
    }
}
