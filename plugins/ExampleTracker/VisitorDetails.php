<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleTracker;

use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['myCustomVisitParam'] = isset($this->details['example_visit_dimension']) ? $this->details['example_visit_dimension'] : 'no-value';
    }

    public function renderIcons($visitorDetails)
    {
        if (empty($visitorDetails['myCustomVisitParam'])) {
            return '';
        }

        $view         = new View('@ExampleTracker/_visitorLogIcons');
        $view->myCustomVisitParam = $visitorDetails['myCustomVisitParam'];
        return $view->render();
    }
}
