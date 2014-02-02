<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Piwik\Filesystem;

class ServerFilesGenerator
{

    /**
     * Generate Apache .htaccess files to restrict access
     */
    public static function createHtAccessFiles()
    {
        // deny access to these folders
        $directoriesToProtect = array(
            '/config',
            '/core',
            '/lang',
            '/tmp',
        );
        foreach ($directoriesToProtect as $directoryToProtect) {
            Filesystem::createHtAccess(PIWIK_INCLUDE_PATH . $directoryToProtect, $overwrite = true);
        }

        // Allow/Deny lives in different modules depending on the Apache version
        $allow = "<IfModule mod_access.c>\nAllow from all\n</IfModule>\n<IfModule !mod_access_compat>\n<IfModule mod_authz_host.c>\nAllow from all\n</IfModule>\n</IfModule>\n<IfModule mod_access_compat>\nAllow from all\n</IfModule>\n";
        $deny = "<IfModule mod_access.c>\nDeny from all\n</IfModule>\n<IfModule !mod_access_compat>\n<IfModule mod_authz_host.c>\nDeny from all\n</IfModule>\n</IfModule>\n<IfModule mod_access_compat>\nDeny from all\n</IfModule>\n";

        // more selective allow/deny filters
        $allowAny = "<Files \"*\">\n" . $allow . "Satisfy any\n</Files>\n";
        $allowStaticAssets = "<Files ~ \"\\.(test\.php|gif|ico|jpg|png|svg|js|css|swf)$\">\n" . $allow . "Satisfy any\n</Files>\n";
        $denyDirectPhp = "<Files ~ \"\\.(php|php4|php5|inc|tpl|in|twig)$\">\n" . $deny . "</Files>\n";

        $directoriesToProtect = array(
            '/js'        => $allowAny,
            '/libs'      => $denyDirectPhp . $allowStaticAssets,
            '/vendor'    => $denyDirectPhp . $allowStaticAssets,
            '/plugins'   => $denyDirectPhp . $allowStaticAssets,
            '/misc/user' => $denyDirectPhp . $allowStaticAssets,
        );
        foreach ($directoriesToProtect as $directoryToProtect => $content) {
            Filesystem::createHtAccess(PIWIK_INCLUDE_PATH . $directoryToProtect, $overwrite = true, $content);
        }
    }

    /**
     * Generate IIS web.config files to restrict access
     *
     * Note: for IIS 7 and above
     */
    public static function createWebConfigFiles()
    {
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
}
