<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\CustomJsTracker\Exception\AccessDeniedException;
use Piwik\Plugins\CustomJsTracker\TrackerUpdater;

class FileIntegrity
{

    /**
     * Get file integrity information
     *
     * @return array(bool $success, array $messages)
     */
    public static function getFileIntegrityInformation()
    {
        $messages = array();

        self::loadManifest();

        if (!class_exists('Piwik\\Manifest')) {
            $messages[] = Piwik::translate('General_WarningFileIntegrityNoManifest')
                . '<br/>'
                . Piwik::translate('General_WarningFileIntegrityNoManifestDeployingFromGit');

            return array(
                $success = false,
                $messages
            );
        }


        $messages = self::getMessagesDirectoriesFoundButNotExpected($messages);

        $messages = self::getMessagesFilesFoundButNotExpected($messages);

        $messages = self::getMessagesFilesMismatch($messages);

        return array(
            $success = empty($messages),
            $messages
        );
    }

    /**
     * Return just a list of the unexpected files
     *
     * @return array
     */
    public static function getUnexpectedFilesList(): array
    {
        self::loadManifest();
        $files = self::getFilesFoundButNotExpected();
        return $files;
    }

    /**
     * Include the manifest
     *
     * @return void
     */
    private static function loadManifest(): void
    {
        $manifest = PIWIK_INCLUDE_PATH . '/config/manifest.inc.php';

        if (file_exists($manifest)) {
            require_once $manifest;
        }
    }

    protected static function getFilesNotInManifestButExpectedAnyway()
    {
        return StaticContainer::get('fileintegrity.ignore');
    }

    protected static function getMessagesDirectoriesFoundButNotExpected($messages)
    {
        $directoriesFoundButNotExpected = self::getDirectoriesFoundButNotExpected();
        if (count($directoriesFoundButNotExpected) > 0) {

            $messageDirectoriesToDelete = '';
            foreach ($directoriesFoundButNotExpected as $directoryFoundNotExpected) {
                $messageDirectoriesToDelete .= Piwik::translate('General_ExceptionDirectoryToDelete', htmlspecialchars($directoryFoundNotExpected)) . '<br/>';
            }

            $directories = array();
            foreach ($directoriesFoundButNotExpected as $directoryFoundNotExpected) {
                $directories[] = htmlspecialchars(realpath($directoryFoundNotExpected));
            }

            $deleteAllAtOnce = array();
            $chunks = array_chunk($directories, 50);

            $command = 'rm -Rf';

            if (SettingsServer::isWindows()) {
                $command = 'rmdir /s /q';
            }

            foreach ($chunks as $directories) {
                $deleteAllAtOnce[] = sprintf('%s %s', $command, implode(' ', $directories));
            }

            $messages[] = Piwik::translate('General_ExceptionUnexpectedDirectory')
                . '<br/>'
                . '--> ' . Piwik::translate('General_ExceptionUnexpectedDirectoryPleaseDelete') . ' <--'
                . '<br/><br/>'
                . $messageDirectoriesToDelete
                . '<br/><br/>'
                . Piwik::translate('General_ToDeleteAllDirectoriesRunThisCommand')
                . '<br/>'
                . implode('<br />', $deleteAllAtOnce)
                . '<br/><br/>';

        }

        return $messages;
    }

    /**
     * @param $messages
     * @return array
     */
    protected static function getMessagesFilesFoundButNotExpected($messages)
    {
        $filesFoundButNotExpected = self::getFilesFoundButNotExpected();
        if (count($filesFoundButNotExpected) > 0) {

            $messageFilesToDelete = '';
            foreach ($filesFoundButNotExpected as $fileFoundNotExpected) {
                $messageFilesToDelete .= Piwik::translate('General_ExceptionFileToDelete', htmlspecialchars($fileFoundNotExpected)) . '<br/>';
            }

            $files = array();
            foreach ($filesFoundButNotExpected as $fileFoundNotExpected) {
                $files[] = '"' . htmlspecialchars(realpath($fileFoundNotExpected)) . '"';
            }

            $deleteAllAtOnce = array();
            $chunks = array_chunk($files, 50);

            $command = 'rm';

            if (SettingsServer::isWindows()) {
                $command = 'del';
            }

            foreach ($chunks as $files) {
                $deleteAllAtOnce[] = sprintf('%s %s', $command, implode(' ', $files));
            }

            $messages[] = Piwik::translate('General_ExceptionUnexpectedFile')
                . '<br/>'
                . '--> ' . Piwik::translate('General_ExceptionUnexpectedFilePleaseDelete') . ' <--'
                . '<br/><br/>'
                . $messageFilesToDelete
                . '<br/><br/>'
                . Piwik::translate('General_ToDeleteAllFilesRunThisCommand')
                . '<br/>'
                . implode('<br />', $deleteAllAtOnce)
                . '<br/><br/>';

            return $messages;

        }
        return $messages;
    }

    /**
     * Look for whole directories which are in the filesystem, but should not be
     *
     * @return array
     */
    protected static function getDirectoriesFoundButNotExpected()
    {
        static $cache = null;
        if(!is_null($cache)) {
            return $cache;
        }

        $pluginsInManifest = self::getPluginsFoundInManifest();
        $directoriesInManifest = self::getDirectoriesFoundInManifest();
        $directoriesFoundButNotExpected = array();

        foreach (self::getPathsToInvestigate() as $file) {
            $file = substr($file, strlen(PIWIK_DOCUMENT_ROOT)); // remove piwik path to match format in manifest.inc.php
            $file = ltrim($file, "\\/");
            $directory = dirname($file);

            if(in_array($directory, $directoriesInManifest)) {
                continue;
            }

            if (self::isFileNotInManifestButExpectedAnyway($file)) {
                continue;
            }
            if (self::isFileFromPluginNotInManifest($file, $pluginsInManifest)) {
                continue;
            }

            if (!in_array($directory, $directoriesFoundButNotExpected)) {
                $directoriesFoundButNotExpected[] = $directory;
            }
        }

        $cache = self::getParentDirectoriesFromListOfDirectories($directoriesFoundButNotExpected);
        return $cache;
    }
    /**
     * Look for files which are in the filesystem, but should not be
     *
     * @return array
     */
    protected static function getFilesFoundButNotExpected()
    {
        $files = \Piwik\Manifest::$files;
        $pluginsInManifest = self::getPluginsFoundInManifest();

        $filesFoundButNotExpected = array();

        foreach (self::getPathsToInvestigate() as $file) {
            if (is_dir($file)) {
                continue;
            }
            $file = substr($file, strlen(PIWIK_DOCUMENT_ROOT)); // remove piwik path to match format in manifest.inc.php
            $file = ltrim($file, "\\/");

            if (self::isFileFromPluginNotInManifest($file, $pluginsInManifest)) {
                continue;
            }
            if (self::isFileNotInManifestButExpectedAnyway($file)) {
                continue;
            }
            if (self::isFileFromDirectoryThatShouldBeDeleted($file)) {
                // we already report the directory as "Directory to delete" so no need to repeat the instruction for each file
                continue;
            }

            if (!isset($files[$file])) {
                $filesFoundButNotExpected[] = $file;
            }
        }

        return $filesFoundButNotExpected;
    }


    protected static function isFileFromDirectoryThatShouldBeDeleted($file)
    {
        $directoriesWillBeDeleted = self::getDirectoriesFoundButNotExpected();
        foreach($directoriesWillBeDeleted as $directoryWillBeDeleted) {
            if(strpos($file, $directoryWillBeDeleted) === 0) {
                return true;
            }
        }
        return false;
    }

    protected static function getDirectoriesFoundInManifest()
    {
        $files = \Piwik\Manifest::$files;

        $directories = array();
        foreach($files as $file => $manifestIntegrityInfo) {
            $directory = $file;

            // add this directory and each parent directory
            while( ($directory = dirname($directory)) && $directory != '.' && $directory != '/') {
                $directories[] = $directory;
            }
        }
        $directories = array_unique($directories);
        return $directories;
    }

    protected static function getPluginsFoundInManifest()
    {
        $files = \Piwik\Manifest::$files;

        $pluginsInManifest = array();
        foreach($files as $file => $manifestIntegrityInfo) {
            if(strpos($file, 'plugins/') === 0) {
                $pluginName = self::getPluginNameFromFilepath($file);
                $pluginsInManifest[] = $pluginName;
            }
        }
        return $pluginsInManifest;
    }

    /**
     * If a plugin folder is not tracked in the manifest then we don't try to report any files in this folder
     * Could be a third party plugin or any plugin from the Marketplace
     *
     * @param $file
     * @param $pluginsInManifest
     * @return bool
     */
    protected static function isFileFromPluginNotInManifest($file, $pluginsInManifest)
    {
        if (strpos($file, 'plugins/') !== 0) {
            return false;
        }

        if (substr_count($file, '/') < 2) {
            // must be a file plugins/abc.xyz and not a plugin directory
            return false;
        }

        $pluginName = self::getPluginNameFromFilepath($file);
        if(in_array($pluginName, $pluginsInManifest)) {
            return false;
        }

        return true;
    }

    protected static function isFileNotInManifestButExpectedAnyway($file)
    {
        $expected = self::getFilesNotInManifestButExpectedAnyway();
        foreach ($expected as $expectedPattern) {
            if (fnmatch($expectedPattern, $file, defined('FNM_CASEFOLD') ? FNM_CASEFOLD : 0)) {
                return true;
            }
        }
        return false;
    }

    protected static function getMessagesFilesMismatch($messages)
    {
        $messagesMismatch = array();
        $hasMd5file = function_exists('md5_file');
        $files = \Piwik\Manifest::$files;
        $hasMd5 = function_exists('md5');
        foreach ($files as $path => $props) {
            $file = PIWIK_INCLUDE_PATH . '/' . $path;

            if (!file_exists($file) || !is_readable($file)) {
                $messagesMismatch[] = Piwik::translate('General_ExceptionMissingFile', $file);
            } elseif (filesize($file) != $props[0]) {

                if (self::isModifiedPathValid($path)) {
                    continue;
                }

                if (!$hasMd5 || in_array(substr($path, -4), array('.gif', '.ico', '.jpg', '.png', '.swf'))) {
                    // files that contain binary data (e.g., images) must match the file size
                    $messagesMismatch[] = Piwik::translate('General_ExceptionFilesizeMismatch', array($file, $props[0], filesize($file)));
                } else {
                    // convert end-of-line characters and re-test text files
                    $content = @file_get_contents($file);
                    $content = str_replace("\r\n", "\n", $content);
                    if ((strlen($content) != $props[0])
                        || (@md5($content) !== $props[1])
                    ) {
                        $messagesMismatch[] = Piwik::translate('General_ExceptionFilesizeMismatch', array($file, $props[0], filesize($file)));
                    }
                }
            } elseif ($hasMd5file && (@md5_file($file) !== $props[1])) {
                if (self::isModifiedPathValid($path)) {
                    continue;
                }

                $messagesMismatch[] = Piwik::translate('General_ExceptionFileIntegrity', $file);
            }
        }

        if (!$hasMd5file) {
            $messages[] = Piwik::translate('General_WarningFileIntegrityNoMd5file');
        }

        if (!empty($messagesMismatch)) {
            $messages[] = Piwik::translate('General_FileIntegrityWarningReupload');
            $messages[] = '--> ' . Piwik::translate('General_FileIntegrityWarningReuploadBis') . ' <--<br/>';
            $messages = array_merge($messages, $messagesMismatch);
        }

        return $messages;
    }

    protected static function isModifiedPathValid($path)
    {
        if ($path === 'piwik.js' || $path === 'matomo.js') {
            // we could have used a postEvent hook to enrich "\Piwik\Manifest::$files;" which would also benefit plugins
            // that want to check for file integrity but we do not want to risk to break anything right now. It is not
            // as trivial because piwik.js might be already updated, or updated on the next request. We cannot define
            // 2 or 3 different filesizes and md5 hashes for one file so we check it here.

            if (Plugin\Manager::getInstance()->isPluginActivated('CustomJsTracker')) {
                $trackerUpdater = new TrackerUpdater();

                if ($trackerUpdater->getCurrentTrackerFileContent() === $trackerUpdater->getUpdatedTrackerFileContent()) {
                    // file was already updated, eg manually or via custom piwik.js, this is a valid piwik.js file as
                    // it was enriched by tracker plugins
                    return true;
                }

                try {
                    // the piwik.js tracker file was not updated yet, but may be updated just after the update by
                    // one of the events CustomJsTracker is listening to or by a scheduled task.
                    // In this case, we check whether such an update will succeed later and if it will, the file is
                    // valid as well as it will be updated on the next request
                    $trackerUpdater->checkWillSucceed();
                    return true;
                } catch (AccessDeniedException $e) {
                    return false;
                }

            }
        }

        return false;
    }

    protected static function getPluginNameFromFilepath($file)
    {
        $pathRelativeToPlugins = substr($file, strlen('plugins/'));
        $pluginName = substr($pathRelativeToPlugins, 0, strpos($pathRelativeToPlugins, '/'));
        return $pluginName;
    }

    /**
     * @return array
     */
    protected static function getPathsToInvestigate()
    {
        $filesToInvestigate = array_merge(
        // all normal files
            Filesystem::globr(PIWIK_DOCUMENT_ROOT, '*'),
            // all hidden files
            Filesystem::globr(PIWIK_DOCUMENT_ROOT, '.*')
        );
        return $filesToInvestigate;
    }

    /**
     * @param $directoriesFoundButNotExpected
     * @return array
     */
    protected static function getParentDirectoriesFromListOfDirectories($directoriesFoundButNotExpected)
    {
        sort($directoriesFoundButNotExpected);

        $parentDirectoriesOnly = array();
        foreach ($directoriesFoundButNotExpected as $directory) {
            $directoryParent = self::getDirectoryParentFromList($directory, $directoriesFoundButNotExpected);
            if($directoryParent) {
                $parentDirectoriesOnly[] = $directoryParent;
            }
        }
        $parentDirectoriesOnly = array_unique($parentDirectoriesOnly);

        return $parentDirectoriesOnly;
    }

    /**
     * When the parent directory of $directory is found within $directories, return it.
     *
     * @param $directory
     * @param $directories
     * @return string
     */
    protected static function getDirectoryParentFromList($directory, $directories)
    {
        foreach($directories as $directoryMaybeParent) {
            if ($directory == $directoryMaybeParent) {
                continue;
            }

            $isParentDirectory = strpos($directory, $directoryMaybeParent) === 0;
            if ($isParentDirectory) {
                return $directoryMaybeParent;
            }
        }
        return null;
    }

}
