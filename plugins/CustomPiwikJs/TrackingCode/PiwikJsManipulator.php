<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs\TrackingCode;

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
