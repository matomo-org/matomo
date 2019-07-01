<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Config;

use Piwik\Cache\Backend\File;
use Piwik\Url;

/**
 * Exception thrown when the config file doesn't exist.
 */
class Cache extends File
{
    public function __construct()
    {
        $host = Url::getHost($checkIfTrusted = false);
        $host = Url::getHostSanitized($host); // Remove any port number to get actual hostname

        $dir = PIWIK_INCLUDE_PATH . '/tmp/' . $host . '/cache/tracker/';
        parent::__construct($dir);
        $this->setNamespace('');
    }

}
