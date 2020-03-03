<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Annotations;

/**
 * Annotations plugins. Provides the ability to attach text notes to
 * dates for each sites. Notes can be viewed, modified, deleted or starred.
 *
 */
class Annotations extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Intl_Today';
    }

    /**
     * Adds css files for this plugin to the list in the event notification.
     *
     * @param array $stylesheets
     */
    public function getStylesheetFiles(array &$stylesheets)
    {
        $stylesheets[] = "plugins/Annotations/stylesheets/annotations.less";
    }

    /**
     * Adds js files for this plugin to the list in the event notification.
     *
     * @param array $jsFiles
     */
    public function getJsFiles(array &$jsFiles)
    {
        $jsFiles[] = "plugins/Annotations/javascripts/annotations.js";
    }
}
