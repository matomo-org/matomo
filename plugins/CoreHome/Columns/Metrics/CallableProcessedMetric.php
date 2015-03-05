<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Plugin\ProcessedMetric;

class CallableProcessedMetric extends ProcessedMetric
{
    private $name;
    private $callback;
    private $dependentMetrics;

    public function __construct($name, $callback, $dependentMetrics = array())
    {
        $this->name = $name;
        $this->callback = $callback;
        $this->dependentMetrics = $dependentMetrics;
    }

    public function getName()
    {
        return $this->name;
    }

    public function compute(Row $row)
    {
        if ($this->callback) {
            return call_user_func($this->callback, $row);
        }
    }

    public function getTranslatedName()
    {
        return '';
    }

    public function getDependentMetrics()
    {
        return $this->dependentMetrics;
    }
}