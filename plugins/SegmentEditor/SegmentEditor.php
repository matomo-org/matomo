<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\Config;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Version;

/**
 */
class SegmentEditor extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Segments.getKnownSegmentsToArchiveForSite'  => 'getKnownSegmentsToArchiveForSite',
            'Segments.getKnownSegmentsToArchiveAllSites' => 'getKnownSegmentsToArchiveAllSites',
            'AssetManager.getJavaScriptFiles'            => 'getJsFiles',
            'AssetManager.getStylesheetFiles'            => 'getStylesheetFiles',
            'Template.nextToCalendar'                    => 'getSegmentEditorHtml',
        );
    }

    function getSegmentEditorHtml(&$out)
    {
        $selector = new SegmentSelectorControl();
        $out .= $selector->render();
    }

    public function getKnownSegmentsToArchiveAllSites(&$segments)
    {
        $this->getKnownSegmentsToArchiveForSite($segments, $idSite = false);
    }

    /**
     * Adds the pre-processed segments to the list of Segments.
     * Used by CronArchive, ArchiveProcessor\Rules, etc.
     *
     * @param $segments
     * @param $idSite
     */
    public function getKnownSegmentsToArchiveForSite(&$segments, $idSite)
    {
        $model = new Model();
        $segmentToAutoArchive = $model->getSegmentsToAutoArchive($idSite);

        foreach ($segmentToAutoArchive as $segmentInfo) {
            $segments[] = $segmentInfo['definition'];
        }

        $segments = array_unique($segments);
    }

    public function install()
    {
        Model::install();
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/SegmentEditor/javascripts/Segmentation.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/SegmentEditor/stylesheets/segmentation.less";
    }

    /**
     * Returns whether adding segments for all websites is enabled or not.
     *
     * @return bool
     */
    public static function isAddingSegmentsForAllWebsitesEnabled()
    {
        return Config::getInstance()->General['allow_adding_segments_for_all_websites'] == 1;
    }
}
