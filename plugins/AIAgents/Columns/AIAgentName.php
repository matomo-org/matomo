<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Columns;

use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\AIAgents\AIAgents;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class AIAgentName extends VisitDimension
{
    protected $columnName   = 'ai_agent_name';
    protected $columnType   = 'VARCHAR(40) NULL';
    protected $nameSingular = 'AIAgents_AIAgentName';
    protected $segmentName  = 'aiAgentName';
    protected $type         = self::TYPE_TEXT;

    public function __construct()
    {
        $this->suggestedValuesCallback = function () {
            $values    = [];
            $providers = AIAgents::getAvailableAgentProviders();

            foreach ($providers as $provider) {
                $values[] = $provider->getId();
            }

            return $values;
        };
    }

    public function shouldForceNewVisit(Request $request, Visitor $visitor, ?Action $action = null)
    {
        $previousProvider = $visitor->getVisitorColumn($this->columnName);
        $provider         = $this->detectProvider($request);

        if ($provider !== $previousProvider) {
            StaticContainer::get(LoggerInterface::class)->debug(
                'Existing visit detected, but creating new visit because AI agent information is different than last action.'
            );

            return true;
        }

        return false;
    }

    /**
     * @return false
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        // function implementation required to have the column available in shouldForceNewVisit.
        return false;
    }

    /**
     * @return false|string
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->detectProvider($request);
    }

    private function detectProvider(Request $request): ?string
    {
        $providers = AIAgents::getAvailableAgentProviders();

        foreach ($providers as $provider) {
            if ($provider->isDetectedForTrackerRequest($request)) {
                return $provider->getId();
            }
        }

        return null;
    }
}
