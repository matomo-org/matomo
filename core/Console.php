<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Piwik\Plugins\CoreConsole\GeneratePlugin;
use Piwik\Plugins\CoreConsole\RunTests;
use Piwik\Plugins\CoreConsole\WatchLog;
use Symfony\Component\Console\Application;

class Console
{
    public function run()
    {
        $console = new Application();

        $console->add(new RunTests());
        $console->add(new GeneratePlugin());
        $console->add(new WatchLog());

        $console->run();
    }
}
