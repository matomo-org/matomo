<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents;

use Piwik\Plugins\AIAgents\Columns\AIAgentName;
use Piwik\Plugin\Archiver as PluginArchiver;

class Archiver extends PluginArchiver
{
    /**
     * @return array<array{plugin: string, segment: string}>
     */
    public function getDependentSegmentsToArchive(): array
    {
        return [
            ['plugin' => 'VisitsSummary', 'segment' => AIAgentName::SEGMENT_ANY],
            ['plugin' => 'VisitsSummary', 'segment' => AIAgentName::SEGMENT_EMPTY],
        ];
    }
}
