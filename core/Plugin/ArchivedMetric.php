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
    const AGGREGATION_COUNT = 'count';
    const AGGREGATION_SUM = 'sum';

    /**
     * @var Dimension
     */
    private $column;

    /**
     * @var string
     */
    private $aggregation;

    /**
     * @var int
     */
    protected $idSite;

    public function __construct(Dimension $column, $aggregation)
    {
        $this->column = $column;
    }

    public function getCategory()
    {
        return $this->column->getCategory();
    }

    public function getName()
    {
        return $this->aggregation . '_' . $this->column->getMetricId();
    }

    public function compute(Row $row)
    {
        switch ($this->column->getType()) {
            case Dimension::TYPE_MONEY:
                return round($this->getMetric($row, $this->column->getMetricId()), 2);

            case Dimension::TYPE_DURATION_S:
            case Dimension::TYPE_DURATION_MS:
                return (int) $this->getMetric($row, $this->column->getMetricId());
        }

        return $this->getMetric($row, $this->column->getMetricId());
    }

    public function format($value, Formatter $formatter)
    {
        switch ($this->column->getType()) {
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
        return $this->column->getName();
    }

    public function getDocumentation()
    {
        switch ($this->aggregation) {
            case self::AGGREGATION_COUNT;
                return 'The number of ' . $this->column->getNamePlural();
            case self::AGGREGATION_SUM;
                return 'The sum of ' . $this->column->getNamePlural();
        }
    }

    public function getTable()
    {
        return $this->column->getDbTableName();
    }

    public function getQuery()
    {
        return $this->aggregation . '(' . $this->column->getDbTableName() . '.'  . $this->column->getColumnName() .')';
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
