<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns\Metrics;

use Piwik\Archive\DataTableFactory;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\ProcessedMetric;

class Number extends ProcessedMetric
{
    /**
     * @var string
     */
    protected $metric;

    /**
     * @var string
     */
    protected $translation;

    /**
     * @var int
     */
    protected $idSite;

    public function __construct($metricName, $metricTranslation)
    {
        $this->metric = $metricName;
        $this->translation = $metricTranslation;
    }

    public function getName()
    {
        return $this->metric;
    }

    public function compute(Row $row)
    {
        return $this->getMetric($row, $this->metric);
    }

    public function getTranslatedName()
    {
        return $this->translation;
    }

    public function getDependentMetrics()
    {
        return array($this->metric);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyNumber($value);
    }

    public function beforeFormat($report, DataTable $table)
    {
        $this->idSite = DataTableFactory::getSiteIdFromMetadata($table);
        if (empty($this->idSite)) {
            $this->idSite = Common::getRequestVar('idSite', 0, 'int');
        }
        return !empty($this->idSite); // skip formatting if there is no site to get currency info from
    }
}