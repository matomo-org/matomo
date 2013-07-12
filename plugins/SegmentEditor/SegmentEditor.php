<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_SegmentEditor
 */

/**
 * @package Piwik_SegmentEditor
 */
class Piwik_SegmentEditor extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => 'Create and reuse custom visitor Segments with the Segment Editor.',
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Piwik.getKnownSegmentsToArchiveForSite'  => 'getKnownSegmentsToArchiveForSite',
            'Piwik.getKnownSegmentsToArchiveAllSites' => 'getKnownSegmentsToArchiveAllSites',
            'AssetManager.getJsFiles'                 => 'getJsFiles',
            'AssetManager.getCssFiles'                => 'getCssFiles',
            'template_nextToCalendar'                 => 'getSegmentEditorHtml',
        );
    }

    function getSegmentEditorHtml(&$out)
    {
        $controller = new Piwik_SegmentEditor_Controller();
        $out .= $controller->getSelector();
    }

    public function getKnownSegmentsToArchiveAllSites(&$segments)
    {
        $segmentsToAutoArchive = Piwik_SegmentEditor_API::getInstance()->getAll($idSite = false, $returnAutoArchived = true);
        foreach ($segmentsToAutoArchive as $segment) {
            $segments[] = $segment['definition'];
        }
    }

    public function getKnownSegmentsToArchiveForSite(&$segments, $idSite)
    {
        $segmentToAutoArchive = Piwik_SegmentEditor_API::getInstance()->getAll($idSite, $returnAutoArchived = true);

        foreach ($segmentToAutoArchive as $segmentInfo) {
            $segments[] = $segmentInfo['definition'];
        }
        $segments = array_unique($segments);
    }

    public function install()
    {
        $queries[] = 'CREATE TABLE `' . Piwik_Common::prefixTable('segment') . '` (
					`idsegment` INT(11) NOT NULL AUTO_INCREMENT,
					`name` VARCHAR(255) NOT NULL,
					`definition` TEXT NOT NULL,
					`login` VARCHAR(100) NOT NULL,
					`enable_all_users` tinyint(4) NOT NULL default 0,
					`enable_only_idsite` INTEGER(11) NULL,
					`auto_archive` tinyint(4) NOT NULL default 0,
					`ts_created` TIMESTAMP NULL,
					`ts_last_edit` TIMESTAMP NULL,
					`deleted` tinyint(4) NOT NULL default 0,
					PRIMARY KEY (`idsegment`)
				) DEFAULT CHARSET=utf8';
        try {
            foreach ($queries as $query) {
                Piwik_Exec($query);
            }
        } catch (Exception $e) {
            if (!Zend_Registry::get('db')->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/SegmentEditor/javascripts/jquery.jscrollpane.js";
        $jsFiles[] = "plugins/SegmentEditor/javascripts/Segmentation.js";
        $jsFiles[] = "plugins/SegmentEditor/javascripts/jquery.mousewheel.js";
        $jsFiles[] = "plugins/SegmentEditor/javascripts/mwheelIntent.js";
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/SegmentEditor/stylesheets/segmentation.css";
        $cssFiles[] = "plugins/SegmentEditor/stylesheets/jquery.jscrollpane.css";
        $cssFiles[] = "plugins/SegmentEditor/stylesheets/scroll.css";
    }
}
