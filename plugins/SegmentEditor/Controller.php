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

/**
 * @package SegmentEditor
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function getSelector()
    {
        $selector = new SegmentSelectorControl();
        return $selector->render();
    }
}
