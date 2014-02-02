<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

/**
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function getSelector()
    {
        $selector = new SegmentSelectorControl();
        return $selector->render();
    }
}
