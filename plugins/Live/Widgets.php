<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\Piwik;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'Live!';

    public function init()
    {
        $this->addWidget('Live_VisitorsInRealTime', 'widget');

        // the visitor profile uses a segment that is not accessible to the anonymous user, so don't bother showing this widget
        if (!Piwik::isUserIsAnonymous()) {
            $this->addWidget('Live_VisitorProfile', 'getVisitorProfilePopup');
        }
    }

}
