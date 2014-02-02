<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleCommand;

/**
 */
class ExampleCommand extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Console.addCommands' => 'addConsoleCommands'
        );
    }

    public function addConsoleCommands(&$commands)
    {
        $commands[] = 'Piwik\Plugins\ExampleCommand\Commands\HelloWorld';
    }
}
