<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Plugins\CustomPiwikJs\Exception\AccessDeniedException;
use Piwik\Plugins\CustomPiwikJs\TrackerUpdater;

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

        $manifest = PIWIK_INCLUDE_PATH . '/config/manifest.inc.php';

        if (file_exists($manifest)) {
            require_once $manifest;
        }

        if (!class_exists('Piwik\\Manifest')) {
            $messages[] = Piwik::translate('General_WarningFileIntegrityNoManifest')
                . ' '
                . Piwik::translate('General_WarningFileIntegrityNoManifestDeployingFromGit');

            return array(
                $success = true,
                $messages
            );
        }

        $messages = self::getMessagesFilesFoundButNotExpected($messages);

        $messages = self::getMessagesFilesMismatch($messages);

        return array(
            $success = empty($messages),
            $messages
        );
    }

    protected static function getFilesNotInManifestButExpectedAnyway()
    {
        return array(
            '*/.htaccess',
            '*/web.config',
            'bootstrap.php',
            'favicon.ico',
            'robots.txt',
            'config/config.ini.php',
            'config/common.ini.php',
            'config/*.config.ini.php',
            'config/manifest.inc.php',
            'misc/*.dat',
            'misc/*.dat.gz',
            'misc/user/*png',
            'misc/package/WebAppGallery/*.xml',
            'misc/package/WebAppGallery/install.sql',
            'vendor/autoload.php',
            'vendor/composer/autoload_real.php',
            'tmp/*',
        );
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
                $messageFilesToDelete .= Piwik::translate('General_ExceptionFileToDelete', $fileFoundNotExpected) . '<br/>';
            }
            $messages[] = Piwik::translate('General_ExceptionUnexpectedFile')
                . '<br/>'
                . '--> ' . Piwik::translate('General_ExceptionUnexpectedFilePleaseDelete') . ' <--'
                . '<br/><br/>'
                . $messageFilesToDelete
                . '<br/>';
            return $messages;

        }
        return $messages;
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

        $filesToInvestigate = array_merge(
            // all normal files
            Filesystem::globr('.', '*'),
            // all hidden files
            Filesystem::globr('.', '.*')
        );
        foreach ($filesToInvestigate as $file) {
            if (is_dir($file)) {
                continue;
            }
            $file = substr($file, 2); // remove starting characters ./ to match format in manifest.inc.php

            if (self::isFileFromPluginNotInManifest($file, $pluginsInManifest)) {
                continue;
            }
            if (self::isFileNotInManifestButExpectedAnyway($file)) {
                continue;
            }

            if (!isset($files[$file])) {
                $filesFoundButNotExpected[] = $file;
            }
        }

        return $filesFoundButNotExpected;
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
            if (fnmatch($expectedPattern, $file)) {
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
            $messages[] = Piwik::translate('General_FileIntegrityWarningReuploadBis') . '<br/>';
            $messages = array_merge($messages, $messagesMismatch);
        }

        return $messages;
    }

    protected static function isModifiedPathValid($path)
    {
        if ($path === 'piwik.js') {
            // we could have used a postEvent hook to enrich "\Piwik\Manifest::$files;" which would also benefit plugins
            // that want to check for file integrity but we do not want to risk to break anything right now. It is not
            // as trivial because piwik.js might be already updated, or updated on the next request. We cannot define
            // 2 or 3 different filesizes and md5 hashes for one file so we check it here.

            if (Plugin\Manager::getInstance()->isPluginActivated('CustomPiwikJs')) {
                $trackerUpdater = new TrackerUpdater();

                if ($trackerUpdater->getCurrentTrackerFileContent() === $trackerUpdater->getUpdatedTrackerFileContent()) {
                    // file was already updated, eg manually or via custom piwik.js, this is a valid piwik.js file as
                    // it was enriched by tracker plugins
                    return true;
                }

                try {
                    // the piwik.js tracker file was not updated yet, but may be updated just after the update by
                    // one of the events CustomPiwikJs is listening to or by a scheduled task.
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

}