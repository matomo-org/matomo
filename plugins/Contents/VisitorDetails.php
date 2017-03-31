<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Tracker\Action;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function filterActions(&$actions)
    {
        foreach ($actions as $idx => $action) {
            if ($action['type'] == Action::TYPE_CONTENT) {
                unset($actions[$idx]);
                continue;
            }
        }
    }
}