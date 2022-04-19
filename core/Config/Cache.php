<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Config;

use Matomo\Cache\Backend\File;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Url;

/**
 * Exception thrown when the config file doesn't exist.
 */
class Cache extends File
{
    private $host = '';

    public function __construct()
    {
        $this->host = $this->getHost();

        // because the config is not yet loaded we cannot identify the instanceId...
        // need to use the hostname
        $dir = $this->makeCacheDir($this->host);

        parent::__construct($dir);
    }

    private function makeCacheDir($host)
    {
        return PIWIK_INCLUDE_PATH . '/tmp/' . $host . '/cache/tracker';
    }

    public static function hasHostConfig($mergedConfigSettings)
    {
        return isset($mergedConfigSettings['General']['trusted_hosts']) && is_array($mergedConfigSettings['General']['trusted_hosts']);
    }

    public function isValidHost($mergedConfigSettings)
    {
        if (!self::hasHostConfig($mergedConfigSettings)) {
            return false;
        }
        // note: we do not support "enable_trusted_host_check" to keep things secure
        return in_array($this->host, $mergedConfigSettings['General']['trusted_hosts'], true);
    }

    private function getHost()
    {
        $host = Url::getHost($checkIfTrusted = false);
        $host = Url::getHostSanitized($host); // Remove any port number to get actual hostname
        $host = Common::sanitizeInputValue($host);

        if (
            empty($host)
            || strpos($host, '..') !== false
            || strpos($host, '\\') !== false
            || strpos($host, '/') !== false
        ) {
            throw new \Exception('Unsupported host');
        }

        $this->host = $host;

        return $host;
    }

    public function doDelete($id)
    {
        // when the config changes, we need to invalidate the config caches for all configured hosts as well, not only
        // the currently trusted host
        $hosts = Url::getTrustedHosts();
        $initialDir = $this->directory;

        foreach ($hosts as $host) {
            $dir = $this->makeCacheDir($host);
            if (@is_dir($dir)) {
                $this->directory = $dir;
                $success = parent::doDelete($id);
                if ($success) {
                    Piwik::postEvent('Core.configFileDeleted', [$this->getFilename($id)]);
                }
            }
        }

        $this->directory = $initialDir;
    }
}
