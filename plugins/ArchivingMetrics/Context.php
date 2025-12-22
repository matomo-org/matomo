<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ArchivingMetrics;

use Piwik\Period;
use Piwik\Segment;

final class Context
{
    /**
     * @var int
     */
    public $idSite;

    /**
     * @var Period
     */
    public $period;

    /**
     * @var Segment
     */
    public $segment;

    /**
     * @var string
     */
    public $plugin;

    public function __construct(int $idSite, Period $period, Segment $segment, string $plugin)
    {
        $this->idSite = $idSite;
        $this->period = $period;
        $this->segment = $segment;
        $this->plugin = $plugin;
    }

    public function getKey(): string
    {
        return implode('|', [
            $this->idSite,
            $this->period->getLabel(),
            $this->segment->getString(),
            $this->period->getDateTimeStart()->toString('Y-m-d'),
            $this->period->getDateTimeEnd()->toString('Y-m-d'),
            $this->plugin,
        ]);
    }
}
