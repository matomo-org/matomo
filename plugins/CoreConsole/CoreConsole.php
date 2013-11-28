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
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\CodeCoverage';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GenerateApi';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GenerateController';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GeneratePlugin';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GenerateSettings';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GenerateVisualizationPlugin';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GitCommit';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GitPull';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GitPush';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\RunTests';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\WatchLog';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GenerateTest';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\GenerateCommand';
        $commands[] = 'Piwik\Plugins\CoreConsole\Commands\SyncUITestScreenshots';
    }
}