<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Exception\MissingFilePermissionException;

class Filechecks
{
    /**
     * Check if this installation can be auto-updated.
     * For performance, we look for clues rather than an exhaustive test.
     *
     * @return bool
     */
    public static function canAutoUpdate()
    {
        if (!is_writable(PIWIK_INCLUDE_PATH . '/') ||
            !is_writable(PIWIK_DOCUMENT_ROOT . '/index.php') ||
            !is_writable(PIWIK_INCLUDE_PATH . '/core') ||
            !is_writable(PIWIK_DOCUMENT_ROOT . '/config/global.ini.php')
        ) {
            return false;
        }
        return true;
    }

    /**
     * Checks if directories are writable and create them if they do not exist.
     *
     * @param array $directoriesToCheck array of directories to check - if not given default Piwik directories that needs write permission are checked
     * @return array  directory name => true|false (is writable)
     */
    public static function checkDirectoriesWritable($directoriesToCheck)
    {
        $resultCheck = array();
        foreach ($directoriesToCheck as $directoryToCheck) {
            Filesystem::mkdir($directoryToCheck);

            $directory = Filesystem::realpath($directoryToCheck);
            if ($directory !== false) {
                $resultCheck[$directory] = is_writable($directoryToCheck);
            }
        }
        return $resultCheck;
    }

    /**
     * Checks that the directories Piwik needs write access are actually writable
     * Displays a nice error page if permissions are missing on some directories
     *
     * @param array $directoriesToCheck Array of directory names to check
     */
    public static function dieIfDirectoriesNotWritable($directoriesToCheck = null)
    {
        $resultCheck = self::checkDirectoriesWritable($directoriesToCheck);
        if (array_search(false, $resultCheck) === false) {
            return;
        }

        $directoryList = '';
        foreach ($resultCheck as $dir => $bool) {
            $realpath = Filesystem::realpath($dir);
            if (!empty($realpath) && $bool === false) {
                $directoryList .= self::getMakeWritableCommand($realpath);
            }
        }

        // Also give the chown since the chmod is only 755
        if (!SettingsServer::isWindows()) {
            $realpath = Filesystem::realpath(PIWIK_INCLUDE_PATH . '/');
            $directoryList = "<code>chown -R ". self::getUserAndGroup() ." " . $realpath . "</code><br />" . $directoryList;
        }

        if (function_exists('shell_exec')) {
            $currentUser = self::getUser();
            if (!empty($currentUser)) {
                $optionalUserInfo = " (running as user '" . $currentUser . "')";
            }
        }

        $directoryMessage  = "<p><b>Matomo couldn't write to some directories $optionalUserInfo</b>.</p>";
        $directoryMessage .= "<p>Try to Execute the following commands on your server, to allow Write access on these directories"
            . ":</p>"
            . "<blockquote>$directoryList</blockquote>"
            . "<p>If this doesn't work, you can try to create the directories with your FTP software, and set the CHMOD to 0755 (or 0777 if 0755 is not enough). To do so with your FTP software, right click on the directories then click permissions.</p>"
            . "<p>After applying the modifications, you can <a href='index.php'>refresh the page</a>.</p>"
            . "<p>If you need more help, try <a target='_blank' rel='noreferrer noopener' href='https://matomo.org'>Matomo.org</a>.</p>";

        $ex = new MissingFilePermissionException($directoryMessage);
        $ex->setIsHtmlMessage();

        throw $ex;
    }

    /**
     * Returns the help message when the auto update can't run because of missing permissions
     *
     * @return string
     */
    public static function getAutoUpdateMakeWritableMessage()
    {
        $realpath = Filesystem::realpath(PIWIK_INCLUDE_PATH . '/');
        $message = '';
        if (!SettingsServer::isWindows()) {
            $message .= "<br /><code>" . self::getCommandToChangeOwnerOfPiwikFiles() . "</code><br />";
        }
        $message .= self::getMakeWritableCommand($realpath);
        if (!SettingsServer::isWindows()) {
            $message .= '<code>chmod 755 ' . $realpath . '/console</code><br />';
        }
        $message .= 'After you execute these commands (or change permissions via your FTP software), refresh the page and you should be able to use the "Automatic Update" feature.';
        return $message;
    }

    /**
     * Returns friendly error message explaining how to fix permissions
     *
     * @param string $path to the directory missing permissions
     * @return string  Error message
     */
    public static function getErrorMessageMissingPermissions($path)
    {
        $message = "Please check that the web server has enough permission to write to these files/directories:<br />";

        if (SettingsServer::isWindows()) {
            $message .= "On Windows, check that the folder is not read only and is writable.\n
						You can try to execute:<br />";
        } else {
            $message .= "For example, on a GNU/Linux server if your Apache httpd user is "
                        . Common::sanitizeInputValue(self::getUser())
                        . ", you can try to execute:<br />\n"
                        . "<code>chown -R ". Common::sanitizeInputValue(self::getUserAndGroup()) ." " . Common::sanitizeInputValue($path) . "</code><br />";
        }

        $message .= self::getMakeWritableCommand($path);

        return $message;
    }

    public static function getUserAndGroup()
    {
        $user = self::getUser();
        if (!function_exists('shell_exec')) {
            return $user . ':' . $user;
        }

        $group = trim(shell_exec('groups '. $user .' | cut -f3 -d" "'));

        if (empty($group) && function_exists('posix_getegid') && function_exists('posix_getgrgid')) {
            $currentGroupId = posix_getegid();

            $group = posix_getpwuid($currentGroupId);
            if (!empty($group['name'])) {
                $group = $group['name'];
            } else {
                $group = $currentGroupId;
            }
        }

        if (empty($group)) {
            $group = 'www-data';
        }

        return $user . ':' . $group;
    }

    public static function getUser()
    {
        if (function_exists('shell_exec')) {
            return trim(shell_exec('whoami'));
        }

        $currentUser = get_current_user();

        if (empty($currentUser) && function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            $currentUserId = posix_geteuid();

            $user = posix_getpwuid($currentUserId);
            if (!empty($user['name'])) {
                $currentUser = $user['name'];
            } else {
                $currentUser = $currentUserId;
            }
        }

        if (empty($currentUser)) {
            $currentUser = 'www-data';
        }

        return $currentUser;
    }

    /**
     * Returns the help text displayed to suggest which command to run to give writable access to a file or directory
     *
     * @param string $realpath
     * @return string
     */
    private static function getMakeWritableCommand($realpath)
    {
        $realpath = Common::sanitizeInputValue($realpath);
        if (SettingsServer::isWindows()) {
            return "<code>cacls $realpath /t /g " . Common::sanitizeInputValue(self::getUser()) . ":f</code><br />\n";
        }
        return "<code>find $realpath -type f -exec chmod 644 {} \;</code><br /><code>find $realpath -type d -exec chmod 755 {} \;</code><br />";
    }

    /**
     * @return string
     */
    public static function getCommandToChangeOwnerOfPiwikFiles()
    {
        $realpath = Filesystem::realpath(PIWIK_INCLUDE_PATH . '/');
        return "chown -R " . self::getUserAndGroup() . " " . $realpath;
    }

    public static function getOwnerOfPiwikFiles()
    {
        $index = Filesystem::realpath(PIWIK_INCLUDE_PATH . '/index.php');
        $stat = stat($index);
        if (!$stat) {
            return '';
        }

        if (function_exists('posix_getgrgid')) {
            $group = posix_getgrgid($stat[5]);

            if (!empty($group['name'])) {
                $group = $group['name'];
            } else {
                $group = $stat[5];
            }
        } else {
            return '';
        }

        if (function_exists('posix_getpwuid')) {
            $user = posix_getpwuid($stat[4]);
            if (!empty($user['name'])) {
                $user = $user['name'];
            } else {
                $user = $stat[4];
            }
        } else {
            return '';
        }

        return "$user:$group";
    }
}
