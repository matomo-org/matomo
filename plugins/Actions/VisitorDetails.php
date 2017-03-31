<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['searches']     = $this->details['visit_total_searches'];
        $visitor['actions']      = $this->details['visit_total_actions'];
        $visitor['interactions'] = $this->details['visit_total_interactions'];
    }
}