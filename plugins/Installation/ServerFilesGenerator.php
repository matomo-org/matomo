<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Piwik\Filesystem;
use Piwik\SettingsServer;

class ServerFilesGenerator
{
    /**
     * Generate Apache .htaccess files to restrict access
     */
    public static function createHtAccessFiles()
    {
        if (!SettingsServer::isApache()) {
            return;
        }
        $denyAll = self::getDenyAllHtaccessContent();
        $allow = self::getAllowHtaccessContent();

        // more selective allow/deny filters
        $allowAny =
            "# Allow any file in this directory\n" .
            "<Files \"*\">\n" .
                $allow . "\n" .
            "</Files>\n";

        $allowStaticAssets =
            "# Serve HTML files as text/html mime type\n" .
            "AddHandler text/html .html\n" .
            "AddHandler text/html .htm\n\n" .

            "# Allow to serve static files which are safe\n" .
            "<Files ~ \"\\.(gif|ico|jpg|png|svg|js|css|htm|html|swf|mp3|mp4|wav|ogg|avi)$\">\n" .
                 $allow . "\n" .
            "</Files>\n";

        $directoriesToProtect = array(
            '/js'        => $allowAny,
            '/libs'      => $denyAll . $allowStaticAssets,
            '/vendor'    => $denyAll . $allowStaticAssets,
            '/plugins'   => $denyAll . $allowStaticAssets,
            '/misc/user' => $denyAll . $allowStaticAssets,
        );
        foreach ($directoriesToProtect as $directoryToProtect => $content) {
            self::createHtAccess(PIWIK_INCLUDE_PATH . $directoryToProtect, $overwrite = true, $content);
        }

        // deny access to these folders
        $directoriesToProtect = array(
            '/config' => $denyAll,
            '/core' => $denyAll,
            '/lang' => $denyAll,
            '/tmp' => $denyAll,
        );
        foreach ($directoriesToProtect as $directoryToProtect => $content) {
            self::createHtAccess(PIWIK_INCLUDE_PATH . $directoryToProtect, $overwrite = true, $content);
        }
    }

    public static function createHtAccessDenyAll($path)
    {
        self::createHtAccess($path, $overwrite = false, self::getDenyAllHtaccessContent());
    }

    /**
     * Create .htaccess file in specified directory
     *
     * Apache-specific; for IIS @see web.config
     *
     * @param string $path without trailing slash
     * @param bool $overwrite whether to overwrite an existing file or not
     * @param string $content
     */
    protected static function createHtAccess($path, $overwrite = true, $content)
    {
        if (SettingsServer::isApache()) {
            $file = $path . '/.htaccess';
            if ($overwrite || !file_exists($file)) {
                @file_put_contents($file, $content);
            }
        }
    }

    /**
     * Generate IIS web.config files to restrict access
     *
     * Note: for IIS 7 and above
     */
    public static function createWebConfigFiles()
    {
        if (!SettingsServer::isIIS()) {
            return;
        }
        @file_put_contents(PIWIK_INCLUDE_PATH . '/web.config',
            '<?xml version="1.0" encoding="UTF-8"?>
<configuration>
  <system.webServer>
    <security>
      <requestFiltering>
        <hiddenSegments>
          <add segment="config" />
          <add segment="core" />
          <add segment="lang" />
          <add segment="tmp" />
        </hiddenSegments>
        <fileExtensions>
          <add fileExtension=".tpl" allowed="false" />
          <add fileExtension=".twig" allowed="false" />
          <add fileExtension=".php4" allowed="false" />
          <add fileExtension=".php5" allowed="false" />
          <add fileExtension=".inc" allowed="false" />
          <add fileExtension=".in" allowed="false" />
          <add fileExtension=".csv" allowed="false" />
          <add fileExtension=".pdf" allowed="false" />
          <add fileExtension=".log" allowed="false" />
        </fileExtensions>
      </requestFiltering>
    </security>
    <directoryBrowse enabled="false" />
    <defaultDocument>
      <files>
        <remove value="index.php" />
        <add value="index.php" />
      </files>
    </defaultDocument>
    <staticContent>
      <remove fileExtension=".svg" />
      <mimeMap fileExtension=".svg" mimeType="image/svg+xml" />
    </staticContent>
  </system.webServer>
</configuration>');

        // deny direct access to .php files
        $directoriesToProtect = array(
            '/libs',
            '/vendor',
            '/plugins',
        );
        foreach ($directoriesToProtect as $directoryToProtect) {
            @file_put_contents(PIWIK_INCLUDE_PATH . $directoryToProtect . '/web.config',
                '<?xml version="1.0" encoding="UTF-8"?>
<configuration>
  <system.webServer>
    <security>
      <requestFiltering>
        <denyUrlSequences>
          <add sequence=".php" />
        </denyUrlSequences>
      </requestFiltering>
    </security>
  </system.webServer>
</configuration>');
        }
    }

    public static function deleteWebConfigFiles()
    {
        $path = PIWIK_INCLUDE_PATH;
        @unlink($path . '/web.config');
        @unlink($path . '/libs/web.config');
        @unlink($path . '/vendor/web.config');
        @unlink($path . '/plugins/web.config');
    }

    /**
     * Generate default robots.txt, favicon.ico, etc to suppress
     * 404 (Not Found) errors in the web server logs, if Piwik
     * is installed in the web root (or top level of subdomain).
     *
     * @see misc/crossdomain.xml
     */
    public static function createWebRootFiles()
    {
        $filesToCreate = array(
            '/robots.txt',
            '/favicon.ico',
        );
        foreach ($filesToCreate as $file) {
            @file_put_contents(PIWIK_DOCUMENT_ROOT . $file, '');
        }
    }

    /**
     * @return string
     */
    protected static function getDenyAllHtaccessContent()
    {
        $deny = self::getDenyHtaccessContent();
        $denyAll =
            "# First, deny access to all files in this directory\n" .
            "<Files \"*\">\n" .
            $deny . "\n" .
            "</Files>\n";

        return $denyAll;
    }

    /**
     * @return string
     */
    protected static function getDenyHtaccessContent()
    {
# Source: https://github.com/phpbb/phpbb/pull/2386/files#diff-f72a38c4bec79cc6ded3f8e435d6bd55L11
# With Apache 2.4 the "Order, Deny" syntax has been deprecated and moved from
# module mod_authz_host to a new module called mod_access_compat (which may be
# disabled) and a new "Require" syntax has been introduced to mod_authz_host.
# We could just conditionally provide both versions, but unfortunately Apache
# does not explicitly tell us its version if the module mod_version is not
# available. In this case, we check for the availability of module
# mod_authz_core (which should be on 2.4 or higher only) as a best guess.
        $deny = <<<HTACCESS_DENY
<IfModule mod_version.c>
	<IfVersion < 2.4>
		Order Deny,Allow
		Deny from All
	</IfVersion>
	<IfVersion >= 2.4>
		Require all denied
	</IfVersion>
</IfModule>
<IfModule !mod_version.c>
	<IfModule !mod_authz_core.c>
		Order Deny,Allow
		Deny from All
	</IfModule>
	<IfModule mod_authz_core.c>
		Require all denied
	</IfModule>
</IfModule>
HTACCESS_DENY;
        return $deny;
    }

    /**
     * @return string
     */
    protected static function getAllowHtaccessContent()
    {
        $allow = <<<HTACCESS_ALLOW
<IfModule mod_version.c>
	<IfVersion < 2.4>
		Order Allow,Deny
		Allow from All
	</IfVersion>
	<IfVersion >= 2.4>
		Require all granted
	</IfVersion>
</IfModule>
<IfModule !mod_version.c>
	<IfModule !mod_authz_core.c>
		Order Allow,Deny
		Allow from All
	</IfModule>
	<IfModule mod_authz_core.c>
		Require all granted
	</IfModule>
</IfModule>
HTACCESS_ALLOW;
        return $allow;
    }

    /**
     * Deletes all existing .htaccess files and web.config files that Piwik may have created,
     */
    public static function deleteHtAccessFiles()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH, ".htaccess");

        // that match the list of directories we create htaccess files
        // (ie. not the root /.htaccess)
        $directoriesWithAutoHtaccess = array(
            '/js',
            '/libs',
            '/vendor',
            '/plugins',
            '/misc/user',
            '/config',
            '/core',
            '/lang',
            '/tmp',
        );

        foreach ($files as $file) {
            foreach ($directoriesWithAutoHtaccess as $dirToDelete) {
                // only delete the first .htaccess and not the ones in sub-directories
                $pathToDelete = $dirToDelete . '/.htaccess';
                if (strpos($file, $pathToDelete) !== false) {
                    @unlink($file);
                }
            }
        }
    }

}
