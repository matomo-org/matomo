<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package SegmentEditor
 */
namespace Piwik\Plugins\SegmentEditor;

use Exception;
use Piwik\Common;
use Piwik\Db;
use Piwik\Version;

/**
 * @package SegmentEditor
 */
class SegmentEditor extends \Piwik\Plugin
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
            'version'         => Version::VERSION,
            'license'          => 'GPL v3+',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html'
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
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
        $controller = new Controller();
        $out .= $controller->getSelector();
    }

    public function getKnownSegmentsToArchiveAllSites(&$segments)
    {
        $segmentsToAutoArchive = API::getInstance()->getAll($idSite = false, $returnAutoArchived = true);
        foreach ($segmentsToAutoArchive as $segment) {
            $segments[] = $segment['definition'];
        }
    }

    public function getKnownSegmentsToArchiveForSite(&$segments, $idSite)
    {
        $segmentToAutoArchive = API::getInstance()->getAll($idSite, $returnAutoArchived = true);

        foreach ($segmentToAutoArchive as $segmentInfo) {
            $segments[] = $segmentInfo['definition'];
        }
        $segments = array_unique($segments);
    }

    public function install()
    {
        $queries[] = 'CREATE TABLE `' . Common::prefixTable('segment') . '` (
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
                Db::exec($query);
            }
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/SegmentEditor/javascripts/Segmentation.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/SegmentEditor/stylesheets/segmentation.less";
    }
}
