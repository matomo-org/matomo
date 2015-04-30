<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel\GlobalSettingsProvider;

use Piwik\Config;
use Piwik\Config\IniFileChain;
use Piwik\Application\Kernel\GlobalSettingsProvider;

/**
 * Default GlobalSettingsProvider implementation. This provider uses the config.ini.php,
 * common.ini.php and global.ini.php files to provide global settings.
 *
 * At the moment a singleton instance of this class is used in order to get tests to pass.
 */
class IniSettingsProvider implements GlobalSettingsProvider
{
    private static $instance = null;

    /**
     * @var IniFileChain
     */
    protected $iniFileChain;

    /**
     * @var string
     */
    protected $pathGlobal = null;

    /**
     * @var string
     */
    protected $pathCommon = null;

    /**
     * @var string
     */
    protected $pathLocal = null;

    /**
     * @param string|null $pathGlobal Path to the global.ini.php file. Or null to use the default.
     * @param string|null $pathLocal Path to the config.ini.php file. Or null to use the default.
     * @param string|null $pathCommon Path to the common.ini.php file. Or null to use the default.
     */
    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $this->pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
        $this->pathCommon = $pathCommon ?: Config::getCommonConfigPath();
        $this->pathLocal = $pathLocal ?: Config::getLocalConfigPath();

        $this->iniFileChain = new IniFileChain();
        $this->reload();
    }

    public function reload($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $this->pathGlobal = $pathGlobal ?: $this->pathGlobal;
        $this->pathCommon = $pathCommon ?: $this->pathCommon;
        $this->pathLocal = $pathLocal ?: $this->pathLocal;

        $this->iniFileChain->reload(array($this->pathGlobal, $this->pathCommon), $this->pathLocal);
    }

    public function &getSection($name)
    {
        $section =& $this->iniFileChain->get($name);
        return $section;
    }

    public function setSection($name, $value)
    {
        $this->iniFileChain->set($name, $value);
    }

    public function getIniFileChain()
    {
        return $this->iniFileChain;
    }

    public function getPathGlobal()
    {
        return $this->pathGlobal;
    }

    public function getPathLocal()
    {
        return $this->pathLocal;
    }

    public function getPathCommon()
    {
        return $this->pathCommon;
    }

    public static function getSingletonInstance($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        if (self::$instance === null) {
            self::$instance = new IniSettingsProvider($pathGlobal, $pathLocal, $pathCommon);
        } else {
            // sanity check. the parameters should only be non-null when creating the IniSettingsProvider the first time.
            // if it's done after, it may point to a problem in the tests. (tests are the only place where these arguments
            // should be specified)
            if ($pathGlobal !== null
                || $pathLocal !== null
                || $pathCommon !== null
            ) {
                $message = "Unexpected state in IniSettingsProvider::getSingletonInstance: singleton already created but paths supplied:\n";
                $message .= "global = '$pathGlobal', local = '$pathLocal', common = '$pathCommon'\n";
                throw new \Exception($message);
            }
        }

        return self::$instance;
    }

    public static function unsetSingletonInstance()
    {
        self::$instance = null;
    }
}