<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents;

use Piwik\Plugin;
use Piwik\Plugins\AIAgents\Providers\AgentAbstract;
use Piwik\Plugins\AIAgents\Providers\ChatGPT;

class AIAgents extends Plugin
{
    /**
     * @return array<AgentAbstract>
     */
    public static function getAvailableAgentProviders(): array
    {
        return [
            ChatGPT::getInstance(),
        ];
    }
}
