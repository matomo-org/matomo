<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */
namespace Piwik\Plugins\CoreConsole;

/**
 * @package CoreConsole
 */
class CoreConsole extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Console.addCommands' => 'addConsoleCommands'
        );
    }

    public function addConsoleCommands(&$commands)
    {
        $commands[] = 'Piwik\Plugins\CoreConsole\CodeCoverage';
        $commands[] = 'Piwik\Plugins\CoreConsole\GenerateApi';
        $commands[] = 'Piwik\Plugins\CoreConsole\GenerateController';
        $commands[] = 'Piwik\Plugins\CoreConsole\GeneratePlugin';
        $commands[] = 'Piwik\Plugins\CoreConsole\GenerateSettings';
        $commands[] = 'Piwik\Plugins\CoreConsole\GenerateVisualizationPlugin';
        $commands[] = 'Piwik\Plugins\CoreConsole\GitCommit';
        $commands[] = 'Piwik\Plugins\CoreConsole\GitPull';
        $commands[] = 'Piwik\Plugins\CoreConsole\GitPush';
        $commands[] = 'Piwik\Plugins\CoreConsole\RunTests';
        $commands[] = 'Piwik\Plugins\CoreConsole\WatchLog';
        $commands[] = 'Piwik\Plugins\CoreConsole\GenerateTest';
    }
}