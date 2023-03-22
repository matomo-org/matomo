<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugin;

use Piwik\Archive\DataTableFactory;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;

class ArchivedMetric extends Metric
{
    const AGGREGATION_COUNT = 'count(%s)';
    const AGGREGATION_COUNT_PREFIX = 'nb_';
    const AGGREGATION_SUM = 'sum(%s)';
    const AGGREGATION_SUM_PREFIX = 'sum_';
    const AGGREGATION_MAX = 'max(%s)';
    const AGGREGATION_MAX_PREFIX = 'max_';
    const AGGREGATION_MIN = 'min(%s)';
    const AGGREGATION_MIN_PREFIX = 'min_';
    const AGGREGATION_UNIQUE = 'count(distinct %s)';
    const AGGREGATION_UNIQUE_PREFIX = 'nb_uniq_';
    const AGGREGATION_COUNT_WITH_NUMERIC_VALUE = 'sum(if(%s > 0, 1, 0))';
    const AGGREGATION_COUNT_WITH_NUMERIC_VALUE_PREFIX = 'nb_with_';

    /**
     * @var string
     */
    private $aggregation;

    /**
     * @var int
     */
    protected $idSite;

    private $name = '';
    private $type = '';
    private $translatedName = '';
    private $documentation = '';
    private $dbTable = '';
    private $category = '';
    private $query = '';

    /**
     * @var Dimension
     */
    private $dimension;

    public function __construct(Dimension $dimension, $aggregation = false)
    {
        if (!empty($aggregation) && strpos($aggregation, '%s') === false) {
            throw new \Exception(sprintf('The given aggregation for %s.%s needs to include a %%s for the column name', $dimension->getDbTableName(), $dimension->getColumnName()));
        }

        $this->setDimension($dimension);
        $this->setDbTable($dimension->getDbTableName());
        $this->aggregation = $aggregation;
    }

    public function getAggregation()
    {
        return $this->aggregation;
    }

    public function setDimension($dimension)
    {
        $this->dimension = $dimension;
        return $this;
    }

    public function getDimension()
    {
        return $this->dimension;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function getCategoryId()
    {
        return $this->category;
    }

    public function setDbTable($dbTable)
    {
        $this->dbTable = $dbTable;
        return $this;
    }

    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    public function setTranslatedName($name)
    {
        $this->translatedName = $name;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function format($value, Formatter $formatter)
    {
        switch ($this->type) {
            case Dimension::TYPE_BOOL:
                return $formatter->getPrettyNumber($value);
            case Dimension::TYPE_ENUM:
                return $formatter->getPrettyNumber($value);
            case Dimension::TYPE_MONEY:
                return $formatter->getPrettyMoney($value, $this->idSite);
            case Dimension::TYPE_FLOAT:
                return $formatter->getPrettyNumber((float) $value, $precision = 2);
            case Dimension::TYPE_NUMBER:
                return $formatter->getPrettyNumber($value);
            case Dimension::TYPE_DURATION_S:
                return $formatter->getPrettyTimeFromSeconds($value, $displayAsSentence = true);
            case Dimension::TYPE_DURATION_MS:
                $val = round(($value / 1000), ($value / 1000) > 60 ? 0 : 2);
                return $formatter->getPrettyTimeFromSeconds($val, $displayAsSentence = true);
            case Dimension::TYPE_PERCENT:
                return $formatter->getPrettyPercentFromQuotient($value);
            case Dimension::TYPE_BYTE:
                return $formatter->getPrettySizeFromBytes($value);
        }

        return $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTranslatedName()
    {
        if (!empty($this->translatedName)) {
            return Piwik::translate($this->translatedName);
        }

        return $this->translatedName;
    }

    public function getDependentMetrics()
    {
        return array($this->getName());
    }

    public function getDocumentation()
    {
        return $this->documentation;
    }

    public function getDbTableName()
    {
        return $this->dbTable;
    }

    public function getQuery()
    {
        if ($this->query) {
            return $this->query;
        }

        $column = $this->dbTable . '.'  . $this->dimension->getColumnName();

        if ($this->dimension->getSqlSegment()) {
            $column = str_replace($this->dimension->getDbTableName(), $this->dbTable, $this->dimension->getSqlSegment());
        }

        if (!empty($this->aggregation)) {
            return sprintf($this->aggregation, $column);
        }

        return $column;
    }

    public function beforeFormat($report, DataTable $table)
    {
        $this->idSite = DataTableFactory::getSiteIdFromMetadata($table);
        if (empty($this->idSite)) {
            $this->idSite = Common::getRequestVar('idSite', 0, 'int');
        }
        return !empty($this->idSite); // skip formatting if there is no site to get currency info from
    }

    public function getSemanticType(): ?string
    {
        return $this->type;
    }
}
