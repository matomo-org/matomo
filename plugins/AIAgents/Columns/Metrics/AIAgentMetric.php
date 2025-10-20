<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * Processed metric for AIAgents.get API method which just copies VisitsSummary.get
 * metrics as differently named metrics.
 *
 * This metric must be supplied in order to ensure correct formatting for processed
 * metrics that are copied from VisitsSummary.get.
 */
class AIAgentMetric extends ProcessedMetric
{
    private const TRANSLATIONS = [
        'avg_time_on_site_ai_agent'     => 'AIAgents_ColumnAIAgentAverageVisitDuration',
        'avg_time_on_site_human'        => 'AIAgents_ColumnHumanAverageVisitDuration',
        'bounce_rate_ai_agent'          => 'AIAgents_ColumnAIAgentBounceRate',
        'bounce_rate_human'             => 'AIAgents_ColumnHumanBounceRate',
        'nb_actions_per_visit_ai_agent' => 'AIAgents_ColumnAIAgentAvgActionsPerVisit',
        'nb_actions_per_visit_human'    => 'AIAgents_ColumnHumanAvgActionsPerVisit',
    ];

    /**
     * @var ProcessedMetric
     */
    private $wrapped;

    /**
     * @var string
     */
    private $suffix;

    public function __construct(ProcessedMetric $wrapped, string $suffix)
    {
        $this->wrapped = $wrapped;
        $this->suffix  = $suffix;
    }

    public function getName()
    {
        return $this->wrapped->getName() . $this->suffix;
    }

    public function getTranslatedName()
    {
        return Piwik::translate(self::TRANSLATIONS[$this->getName()]);
    }

    public function format($value, Formatter $formatter)
    {
        return $this->wrapped->format($value, $formatter);
    }

    public function compute(Row $row)
    {
        return 0; // (metric is not computed, it is copied from segmented report)
    }

    public function getDependentMetrics()
    {
        return [];
    }

    public function getSemanticType(): ?string
    {
        return $this->wrapped->getSemanticType();
    }
}
