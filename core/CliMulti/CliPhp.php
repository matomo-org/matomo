<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\Common;
use Piwik\Config;

class CliPhp
{

    public function findPhpBinary()
    {
        $general = Config::getInstance()->General;
        if (!empty($general['php_binary_path']) && file_exists($general['php_binary_path'])) {
            return $general['php_binary_path'];
        }

        if (defined('PHP_BINARY')) {
            if ($this->isHhvmBinary(PHP_BINARY)) {
                return PHP_BINARY . ' --php';
            }

            if ($this->isValidPhpType(PHP_BINARY)) {
                return PHP_BINARY . ' -q';
            }
        }

        $bin = '';

        if (!empty($_SERVER['_']) && Common::isPhpCliMode()) {
            $bin = $this->getPhpCommandIfValid($_SERVER['_']);
        }

        if (empty($bin) && !empty($_SERVER['argv'][0]) && Common::isPhpCliMode()) {
            $bin = $this->getPhpCommandIfValid($_SERVER['argv'][0]);
        }

        if (empty($bin)) {
            $possiblePhpPath = PHP_BINDIR . ('\\' === \DIRECTORY_SEPARATOR ? '\\php.exe' : '/php');
            $bin = $this->getPhpCommandIfValid($possiblePhpPath);
        }

        if (!$this->isValidPhpType($bin) && function_exists('shell_exec')) {
            $bin = @shell_exec('which php');
        }

        if (!$this->isValidPhpType($bin)) {
            return false;
        }

        $bin = trim($bin);

        if (!$this->isValidPhpVersion($bin)) {
            return false;
        }

        $bin .= ' -q';

        return $bin;
    }

    private function isHhvmBinary($bin)
    {
        return false !== strpos($bin, 'hhvm');
    }

    private function isValidPhpVersion($bin)
    {
        global $piwik_minimumPHPVersion;
        $cliVersion = $this->getPhpVersion($bin);
        $isCliVersionValid = $cliVersion && version_compare($piwik_minimumPHPVersion, $cliVersion) <= 0;
        return $isCliVersionValid;
    }

    private function isValidPhpType($path)
    {
        if (empty($path)) {
            return false;
        }
        $path = basename($path);

        return false === strpos($path, 'fpm')
        && false === strpos($path, 'cgi')
        && false === strpos($path, 'phpunit')
        && false === strpos($path, 'lsphp');
    }

    private function getPhpCommandIfValid($path)
    {
        if (!empty($path) && @is_executable($path)) {
            if (0 === strpos($path, PHP_BINDIR) && $this->isValidPhpType($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * @param string $bin PHP binary
     * @return string
     */
    private function getPhpVersion($bin)
    {
        $command = sprintf("%s -r 'echo phpversion();'", $bin);
        $version = null;
        if (function_exists('shell_exec')) {
            $version = @shell_exec($command);
        }
        return $version;
    }
}
