<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'Live!';

    public function init()
    {
        $this->addWidget('Live_VisitorsInRealTime', 'widget');
        $this->addWidget('Live_VisitorProfile', 'getVisitorProfilePopup');
    }

}
