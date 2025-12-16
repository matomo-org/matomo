<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ArchivingMetrics;

final class Context
{
    public $idSite;
    public $period;
    public $segment;
    public $date1;
    public $date2;
    public $plugin;

    public function __construct($idSite, $period, $segment, $date1, $date2, $plugin)
    {
        $this->idSite = $idSite;
        $this->period = $period;
        $this->segment = $segment;
        $this->date1 = $date1;
        $this->date2 = $date2;
        $this->plugin = $plugin;
    }

    public function getKey(): string
    {
        return implode('|', [
            $this->idSite,
            $this->period,
            $this->segment,
            $this->date1,
            $this->date2,
            $this->plugin,
        ]);
    }
}
