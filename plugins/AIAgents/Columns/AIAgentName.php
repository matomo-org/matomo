<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class AIAgentName extends VisitDimension
{
    protected $columnName = 'ai_agent_name';
    protected $columnType = 'VARCHAR(40) NULL';

    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }
}
