<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker\TrackingCode;

use Piwik\Piwik;

class PiwikJsManipulator
{
    const HOOK = '/*!!! pluginTrackerHook */';
    /**
     * @var string
     */
    private $content;

    /** @var PluginTrackerFiles */
    private $pluginTrackerFiles;

    public function __construct($content, PluginTrackerFiles $pluginTrackerFiles)
    {
        $this->content = $content;
        $this->pluginTrackerFiles = $pluginTrackerFiles;
    }

    public function manipulateContent()
    {
        $files = $this->pluginTrackerFiles->find();

        foreach ($files as $file) {
            $trackerExtension = $this->getSignatureWithContent($file->getName(), $file->getContent());

            // for some reasons it is /*!!! in piwik.js minified and /*!! in js/piwik.js unminified
            $this->content = str_replace(array(self::HOOK, '/*!! pluginTrackerHook */'), self::HOOK . $trackerExtension, $this->content);
        }

        $content = $this->content;

        /**
         * Triggered after the Matomo JavaScript tracker has been generated and shortly before the tracker file
         * is written to disk. You can listen to this event to for example automatically append some code to the JS
         * tracker file.
         *
         * **Example**
         *
         *     function onManipulateJsTracker (&$content) {
         *         $content .= "\nPiwik.DOM.onLoad(function () { console.log('loaded'); });";
         *     }
         *
         * @param string $content the generated JavaScript tracker code
         */
        Piwik::postEvent('CustomJsTracker.manipulateJsTracker', array(&$content));
        $this->content = $content;

        return $this->content;
    }

    /**
     * @param string $name
     * @param string $content
     * @return string
     */
    private function getSignatureWithContent($name, $content)
    {
        return sprintf(
            "\n\n/* GENERATED: %s */\n%s\n/* END GENERATED: %s */\n",
            $name,
            $content,
            $name
        );
    }

}
