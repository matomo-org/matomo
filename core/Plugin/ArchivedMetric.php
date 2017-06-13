<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugin;

use Piwik\Archive\DataTableFactory;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;

class ArchivedMetric extends Metric
{
    const AGGREGATION_COUNT = 'nb';
    const AGGREGATION_SUM = 'sum';
    const AGGREGATION_UNIQUE = 'uniq';

    /**
     * @var Dimension
     */
    private $dimension;

    /**
     * @var string
     */
    private $aggregation;

    /**
     * @var int
     */
    protected $idSite;

    public function __construct(Dimension $dimension, $aggregation)
    {
        $this->dimension = $dimension;
        $this->aggregation = $aggregation;
    }

    public function getCategory()
    {
        return $this->dimension->getCategory();
    }

    public function getName()
    {
        return $this->aggregation . '_' . $this->dimension->getMetricId();
    }

    public function compute(Row $row)
    {
        switch ($this->dimension->getType()) {
            case Dimension::TYPE_MONEY:
                return round($this->getMetric($row, $this->dimension->getMetricId()), 2);

            case Dimension::TYPE_DURATION_S:
            case Dimension::TYPE_DURATION_MS:
                return (int) $this->getMetric($row, $this->dimension->getMetricId());
        }

        return $this->getMetric($row, $this->dimension->getMetricId());
    }

    public function format($value, Formatter $formatter)
    {
        switch ($this->dimension->getType()) {
            case Dimension::TYPE_MONEY:
                return $formatter->getPrettyMoney($value, $this->idSite);
            case Dimension::TYPE_FLOAT:
                return $formatter->getPrettyNumber($value, $precision = 2);
            case Dimension::TYPE_NUMBER:
                return $formatter->getPrettyNumber($value);
            case Dimension::TYPE_DURATION_S:
                return $formatter->getPrettyTimeFromSeconds($value, $displayAsSentence = false);
            case Dimension::TYPE_DURATION_MS:
                return $formatter->getPrettyTimeFromSeconds($value, $displayAsSentence = true);
        }

        return $value;
    }

    public function getTranslatedName()
    {
        switch ($this->aggregation) {
            case self::AGGREGATION_COUNT;
                return $this->dimension->getNamePlural();
            case self::AGGREGATION_SUM;
                return 'Total ' . $this->dimension->getNamePlural();
            case self::AGGREGATION_UNIQUE;
                return 'Unique ' . $this->dimension->getNamePlural();
        }

        return $this->dimension->getNamePlural();
    }

    public function getDocumentation()
    {
        switch ($this->aggregation) {
            case self::AGGREGATION_COUNT;
                return 'The number of ' . $this->dimension->getNamePlural();
            case self::AGGREGATION_SUM;
                return 'The sum of ' . $this->dimension->getNamePlural();
            case self::AGGREGATION_UNIQUE;
                return 'Unique ' . $this->dimension->getNamePlural();
        }
    }

    public function getDbTableName()
    {
        return $this->dimension->getDbTableName();
    }

    public function getQuery()
    {
        $column = $this->dimension->getDbTableName() . '.'  . $this->dimension->getColumnName();

        if ($this->aggregation === self::AGGREGATION_UNIQUE) {
            return 'count(distinct ' . $column .')';
        } elseif ($this->aggregation === self::AGGREGATION_COUNT) {
            return 'count(' . $column .')';
        }

        return $this->aggregation . '(' . $column .')';
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
