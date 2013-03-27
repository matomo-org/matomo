<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_ExamplePlugin
 */

/**
 *
 * @package Piwik_ExamplePlugin
 */
class Piwik_ExamplePlugin extends Piwik_Plugin
{
    /**
     * Return information about this plugin.
     *
     * @see Piwik_Plugin
     *
     * @return array
     */
    public function getInformation()
    {
        return array(
            'description'          => Piwik_Translate('ExamplePlugin_PluginDescription'),
            'homepage'             => 'http://piwik.org/',
            'author'               => 'Piwik',
            'author_homepage'      => 'http://piwik.org/',
            'license'              => 'GPL v3 or later',
            'license_homepage'     => 'http://www.gnu.org/licenses/gpl.html',
            'version'              => '0.1',
            'translationAvailable' => true,
        );
    }

    public function getListHooksRegistered()
    {
        return array(
//			'Controller.renderView' => 'addUniqueVisitorsColumnToGivenReport',
            'WidgetsList.add' => 'addWidgets',
        );
    }

    function activate()
    {
        // Executed every time plugin is Enabled
    }

    function deactivate()
    {
        // Executed every time plugin is disabled
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function addUniqueVisitorsColumnToGivenReport($notification)
    {
        $view = $notification->getNotificationInfo();
        $view = $view['view'];
        if ($view->getCurrentControllerName() == 'Referers'
            && $view->getCurrentControllerAction() == 'getWebsites'
        ) {
            $view->addColumnToDisplay('nb_uniq_visitors');
        }
    }

    function addWidgets()
    {
        // we register the widgets so they appear in the "Add a new widget" window in the dashboard
        // Note that the first two parameters can be either a normal string, or an index to a translation string
        Piwik_AddWidget('ExamplePlugin_exampleWidgets', 'ExamplePlugin_exampleWidget', 'ExamplePlugin', 'exampleWidget');
        Piwik_AddWidget('ExamplePlugin_exampleWidgets', 'ExamplePlugin_photostreamMatt', 'ExamplePlugin', 'photostreamMatt');
        Piwik_AddWidget('ExamplePlugin_exampleWidgets', 'ExamplePlugin_piwikForumVisits', 'ExamplePlugin', 'piwikDownloads');
    }
}
