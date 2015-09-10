<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Tracker\Cache as TrackerCache;
use Piwik\Cache as PiwikCache;

/**
 * Contains helper functions that deal with the filesystem.
 *
 */
class Filesystem
{
    /**
     * Called on Core install, update, plugin enable/disable
     * Will clear all cache that could be affected by the change in configuration being made
     */
    public static function deleteAllCacheOnUpdate($pluginName = false)
    {
        AssetManager::getInstance()->removeMergedAssets($pluginName);
        View::clearCompiledTemplates();
        TrackerCache::deleteTrackerCache();
        PiwikCache::flushAll();
        self::clearPhpCaches();
    }

    /**
     * ending WITHOUT slash
     *
     * @return string
     */
    public static function getPathToPiwikRoot()
    {
        return realpath(dirname(__FILE__) . "/..");
    }

    /**
     * Returns true if the string is a valid filename
     * File names that start with a-Z or 0-9 and contain a-Z, 0-9, underscore(_), dash(-), and dot(.) will be accepted.
     * File names beginning with anything but a-Z or 0-9 will be rejected (including .htaccess for example).
     * File names containing anything other than above mentioned will also be rejected (file names with spaces won't be accepted).
     *
     * @param string $filename
     * @return bool
     */
    public static function isValidFilename($filename)
    {
        return (0 !== preg_match('/(^[a-zA-Z0-9]+([a-zA-Z_0-9.-]*))$/D', $filename));
    }

    /**
     * Get canonicalized absolute path
     * See http://php.net/realpath
     *
     * @param string $path
     * @return string  canonicalized absolute path
     */
    public static function realpath($path)
    {
        if (file_exists($path)) {
            return realpath($path);
        }
        return $path;
    }

    /**
     * Attempts to create a new directory. All errors are silenced.
     *
     * _Note: This function does **not** create directories recursively._
     *
     * @param string $path The path of the directory to create.
     * @api
     */
    public static function mkdir($path)
    {
        if (!is_dir($path)) {
            // the mode in mkdir is modified by the current umask
            @mkdir($path, self::getChmodForPath($path), $recursive = true);
        }

        // try to overcome restrictive umask (mis-)configuration
        if (!is_writable($path)) {
            @chmod($path, 0755);
            if (!is_writable($path)) {
                @chmod($path, 0775);
                // enough! we're not going to make the directory world-writeable
            }
        }
    }

    /**
     * Checks if the filesystem Piwik stores sessions in is NFS or not. This
     * check is done in order to avoid using file based sessions on NFS system,
     * since on such a filesystem file locking can make file based sessions
     * incredibly slow.
     *
     * Note: In order to figure this out, we try to run the 'df' program. If
     * the 'exec' or 'shell_exec' functions are not available, we can't do
     * the check.
     *
     * @return bool True if on an NFS filesystem, false if otherwise or if we
     *              can't use shell_exec or exec.
     */
    public static function checkIfFileSystemIsNFS()
    {
        $sessionsPath = Session::getSessionsDirectory();

        // this command will display details for the filesystem that holds the $sessionsPath
        // path, but only if its type is NFS. if not NFS, df will return one or less lines
        // and the return code 1. if NFS, it will return 0 and at least 2 lines of text.
        $command = "df -T -t nfs \"$sessionsPath\" 2>&1";

        if (function_exists('exec')) {
            // use exec

            $output = $returnCode = null;
            @exec($command, $output, $returnCode);

            // check if filesystem is NFS
            if ($returnCode == 0
                && count($output) > 1
            ) {
                return true;
            }
        } elseif (function_exists('shell_exec')) {
            // use shell_exec

            $output = @shell_exec($command);
            if ($output) {
                $output = explode("\n", trim($output));

                $commandFailed = (false !== strpos($output, "no file systems processed"));
                if (!$commandFailed
                    && count($output) > 1) {
                    // check if filesystem is NFS
                    return true;
                }
            }
        }

        return false; // not NFS, or we can't run a program to find out
    }

    /**
     * Recursively find pathnames that match a pattern.
     *
     * See {@link http://php.net/manual/en/function.glob.php glob} for more info.
     *
     * @param string $sDir directory The directory to glob in.
     * @param string $sPattern pattern The pattern to match paths against.
     * @param int $nFlags `glob()` . See {@link http://php.net/manual/en/function.glob.php glob()}.
     * @return array The list of paths that match the pattern.
     * @api
     */
    public static function globr($sDir, $sPattern, $nFlags = null)
    {
        if (($aFiles = \_glob("$sDir/$sPattern", $nFlags)) == false) {
            $aFiles = array();
        }
        if (($aDirs = \_glob("$sDir/*", GLOB_ONLYDIR)) != false) {
            foreach ($aDirs as $sSubDir) {
                if (is_link($sSubDir)) {
                    continue;
                }

                $aSubFiles = self::globr($sSubDir, $sPattern, $nFlags);
                $aFiles = array_merge($aFiles, $aSubFiles);
            }
        }
        return $aFiles;
    }

    /**
     * Recursively deletes a directory.
     *
     * @param string $dir Path of the directory to delete.
     * @param boolean $deleteRootToo If true, `$dir` is deleted, otherwise just its contents.
     * @param \Closure|false $beforeUnlink An optional closure to execute on a file path before unlinking.
     * @api
     */
    public static function unlinkRecursive($dir, $deleteRootToo, \Closure $beforeUnlink = null)
    {
        if (!$dh = @opendir($dir)) {
            return;
        }
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }

            $path = $dir . '/' . $obj;
            if ($beforeUnlink) {
                $beforeUnlink($path);
            }

            if (!@unlink($path)) {
                self::unlinkRecursive($path, true);
            }
        }
        closedir($dh);
        if ($deleteRootToo) {
            @rmdir($dir);
        }
        return;
    }

    /**
     * Removes all files and directories that are present in the target directory but are not in the source directory.
     *
     * @param string $source Path to the source directory
     * @param string $target Path to the target
     */
    public static function unlinkTargetFilesNotPresentInSource($source, $target)
    {
        $diff = self::directoryDiff($source, $target);
        $diff = self::sortFilesDescByPathLength($diff);

        foreach ($diff as $file) {
            $remove = $target . $file;

            if (is_dir($remove)) {
                @rmdir($remove);
            } else {
                self::deleteFileIfExists($remove);
            }
        }
    }

    /**
     * Sort all given paths/filenames by its path length. Long path names will be listed first. This method can be
     * useful if you have for instance a bunch of files/directories to delete. By sorting them by lengh you can make
     * sure to delete all files within the folders before deleting the actual folder.
     *
     * @param string[] $files
     * @return string[]
     */
    public static function sortFilesDescByPathLength($files)
    {
        usort($files, function ($a, $b) {
            // sort by filename length so we kinda make sure to remove files before its directories
            if ($a == $b) {
                return 0;
            }

            return (strlen($a) > strlen($b) ? -1 : 1);
        });

        return $files;
    }

    /**
     * Computes the difference of directories. Compares $target against $source and returns a relative path to all files
     * and directories in $target that are not present in $source.
     *
     * @param $source
     * @param $target
     *
     * @return string[]
     */
    public static function directoryDiff($source, $target)
    {
        $sourceFiles = self::globr($source, '*');
        $targetFiles = self::globr($target, '*');

        $sourceFiles = array_map(function ($file) use ($source) {
            return str_replace($source, '', $file);
        }, $sourceFiles);

        $targetFiles = array_map(function ($file) use ($target) {
            return str_replace($target, '', $file);
        }, $targetFiles);

        $diff = array_diff($targetFiles, $sourceFiles);

        return array_values($diff);
    }

    /**
     * Copies a file from `$source` to `$dest`.
     *
     * @param string $source A path to a file, eg. './tmp/latest/index.php'. The file must exist.
     * @param string $dest A path to a file, eg. './index.php'. The file does not have to exist.
     * @param bool $excludePhp Whether to avoid copying files if the file is related to PHP
     *                         (includes .php, .tpl, .twig files).
     * @throws Exception If the file cannot be copied.
     * @return true
     * @api
     */
    public static function copy($source, $dest, $excludePhp = false)
    {
        if ($excludePhp) {
            if (self::hasPHPExtension($source)) {
                return true;
            }
        }

        $success = self::tryToCopyFileAndVerifyItWasCopied($source, $dest);

        if (!$success) {
            $success = self::tryToCopyFileAndVerifyItWasCopied($source, $dest);
        }

        if (!$success) {
            throw new Exception("Error while creating/copying file from $source to <code>$dest</code>. Content of copied file is different.");
        }

        return true;
    }

    private static function hasPHPExtension($file)
    {
        static $phpExtensions = array('php', 'tpl', 'twig');

        $path_parts = pathinfo($file);

        if (!empty($path_parts['extension'])
            && in_array($path_parts['extension'], $phpExtensions)) {
            return true;
        }

        return false;
    }

    /**
     * Copies the contents of a directory recursively from `$source` to `$target`.
     *
     * @param string $source A directory or file to copy, eg. './tmp/latest'.
     * @param string $target A directory to copy to, eg. '.'.
     * @param bool $excludePhp Whether to avoid copying files if the file is related to PHP
     *                         (includes .php, .tpl, .twig files).
     * @throws Exception If a file cannot be copied.
     * @api
     */
    public static function copyRecursive($source, $target, $excludePhp = false)
    {
        if (is_dir($source)) {
            self::mkdir($target);
            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                $sourcePath = $source . '/' . $entry;
                if (is_dir($sourcePath)) {
                    self::copyRecursive($sourcePath, $target . '/' . $entry, $excludePhp);
                    continue;
                }
                $destPath = $target . '/' . $entry;
                self::copy($sourcePath, $destPath, $excludePhp);
            }
            $d->close();
        } else {
            self::copy($source, $target, $excludePhp);
        }
    }

    /**
     * Deletes the given file if it exists.
     *
     * @param  string $pathToFile
     * @return bool   true in case of success or if file does not exist, false otherwise. It might fail in case the
     *                file is not writeable.
     * @api
     */
    public static function deleteFileIfExists($pathToFile)
    {
        if (!file_exists($pathToFile)) {
            return true;
        }

        return @unlink($pathToFile);
    }

    /**
     * Get the size of a file in the specified unit.
     *
     * @param string $pathToFile
     * @param string $unit eg 'B' for Byte, 'KB', 'MB', 'GB', 'TB'.
     *
     * @return float|null Returns null if file does not exist or the size of the file in the specified unit
     *
     * @throws Exception In case the unit is invalid
     */
    public static function getFileSize($pathToFile, $unit = 'B')
    {
        $unit  = strtoupper($unit);
        $units = array('TB' => pow(1024, 4),
                       'GB' => pow(1024, 3),
                       'MB' => pow(1024, 2),
                       'KB' => 1024,
                       'B' => 1);

        if (!array_key_exists($unit, $units)) {
            throw new Exception('Invalid unit given');
        }

        if (!file_exists($pathToFile)) {
            return;
        }

        $filesize  = filesize($pathToFile);
        $factor    = $units[$unit];
        $converted = $filesize / $factor;

        return $converted;
    }

    /**
     * Remove a file.
     *
     * @param string $file
     * @param bool $silenceErrors If true, no exception will be thrown in case removing fails.
     */
    public static function remove($file, $silenceErrors = false)
    {
        if (!file_exists($file)) {
            return;
        }

        $result = @unlink($file);

        // Testing if the file still exist avoids race conditions
        if (!$result && file_exists($file)) {
            if ($silenceErrors) {
                Log::warning('Failed to delete file ' . $file);
            } else {
                throw new \RuntimeException('Unable to delete file ' . $file);
            }
        }
    }

    /**
     * @param $path
     * @return int
     */
    private static function getChmodForPath($path)
    {
        $pathIsTmp = StaticContainer::get('path.tmp');
        if (strpos($path, $pathIsTmp) === 0) {
            // tmp/* folder
            return 0750;
        }
        // plugins/* and all others
        return 0755;
    }

    public static function clearPhpCaches()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache(); // clear the system (aka 'opcode') cache
        }

        if (function_exists('opcache_reset')) {
            @opcache_reset(); // reset the opcode cache (php 5.5.0+)
        }

        if (function_exists('wincache_refresh_if_changed')) {
            @wincache_refresh_if_changed(); // reset the wincache
        }

        if (function_exists('xcache_clear_cache') && defined('XC_TYPE_VAR')) {
            if (ini_get('xcache.admin.enable_auth')) {
                // XCache will not be cleared because "xcache.admin.enable_auth" is enabled in php.ini.
            } else {
                @xcache_clear_cache(XC_TYPE_VAR);
            }
        }
    }

    private static function havePhpFilesSameContent($file1, $file2)
    {
        if (self::hasPHPExtension($file1)) {
            $sourceMd5 = md5_file($file1);
            $destMd5   = md5_file($file2);

            return $sourceMd5 === $destMd5;
        }

        return true;
    }

    private static function tryToCopyFileAndVerifyItWasCopied($source, $dest)
    {
        if (!@copy($source, $dest)) {
            @chmod($dest, 0755);
            if (!@copy($source, $dest)) {
                $message = "Error while creating/copying file to <code>$dest</code>. <br />"
                    . Filechecks::getErrorMessageMissingPermissions(self::getPathToPiwikRoot());
                throw new Exception($message);
            }
        }

        if (file_exists($source) && file_exists($dest)) {
            return self::havePhpFilesSameContent($source, $dest);
        }

        return true;
    }
}
