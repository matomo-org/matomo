<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\CliMulti;
use Piwik\Common;
use Piwik\Plugins\Installation\SystemCheck;

class CliPhp
{

    public function findPhpBinary()
    {
        if (defined('PHP_BINARY')) {

            if($this->isValidPhpType(PHP_BINARY)) {
                return PHP_BINARY . ' -q';
            }

            if($this->isHhvmBinary(PHP_BINARY)) {
                return PHP_BINARY . ' --php';
            }
        }

        $bin = '';

        if (!empty($_SERVER['_']) && Common::isPhpCliMode()) {
            $bin = $this->getPhpCommandIfValid($_SERVER['_']);
        }

        if (empty($bin) && !empty($_SERVER['argv'][0]) && Common::isPhpCliMode()) {
            $bin = $this->getPhpCommandIfValid($_SERVER['argv'][0]);
        }

        if (!$this->isValidPhpType($bin)) {
            $bin = shell_exec('which php');
        }

        if (!$this->isValidPhpType($bin)) {
            $bin = shell_exec('which php5');
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
        $cliVersion = $this->getPhpVersion($bin);
        $isCliVersionValid = SystemCheck::isPhpVersionValid($cliVersion);
        return $isCliVersionValid;
    }

    private function isValidPhpType($path)
    {
        return !empty($path)
        && false === strpos($path, 'fpm')
        && false === strpos($path, 'cgi')
        && false === strpos($path, 'phpunit');
    }

    private function getPhpCommandIfValid($path)
    {
        if (!empty($path) && is_executable($path)) {
            if (0 === strpos($path, PHP_BINDIR) && $this->isValidPhpType($path)) {
                return $path;
            }
        }
    }

    /**
     * @param $bin PHP binary
     * @return string
     */
    private function getPhpVersion($bin)
    {
        $command = sprintf("%s -r 'echo phpversion();'", $bin);
        $version = shell_exec($command);
        return $version;
    }
}
