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
    public function getInformation()
    {
        return array(
            'description'     => 'Create and reuse custom visitor Segments with the Segment Editor.',
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

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

    function getSegmentEditorHtml($notification)
    {
        $out =& $notification->getNotificationObject();
        $controller = new Piwik_SegmentEditor_Controller();
        $out .= $controller->getSelector();
    }

    public function getKnownSegmentsToArchiveAllSites($notification)
    {
        $segments =& $notification->getNotificationObject();
        $segmentToAutoArchive = Piwik_SegmentEditor_API::getInstance()->getAll($idSite = false, $returnAutoArchived = true);
        if (!empty($segmentToAutoArchive)) {
            $segments = array_merge($segments, $segmentToAutoArchive);
        }
    }

    public function getKnownSegmentsToArchiveForSite($notification)
    {
        $segments =& $notification->getNotificationObject();
        $idSite = $notification->getNotificationInfo();
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

    public function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();
        $jsFiles[] = "plugins/SegmentEditor/templates/jquery.jscrollpane.js";
        $jsFiles[] = "plugins/SegmentEditor/templates/Segmentation.js";
        $jsFiles[] = "plugins/SegmentEditor/templates/jquery.mousewheel.js";
        $jsFiles[] = "plugins/SegmentEditor/templates/mwheelIntent.js";
    }

    public function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();
        $cssFiles[] = "plugins/SegmentEditor/templates/Segmentation.css";
        $cssFiles[] = "plugins/SegmentEditor/templates/jquery.jscrollpane.css";
        $cssFiles[] = "plugins/SegmentEditor/templates/scroll.css";
    }

}
