<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\Archive\DataTableFactory;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;

/**
 * Represents a metric that is calculated from a dimension column when archiving, eg, a count or sum of a log
 * table column. Instances are created with {@link \Piwik\Columns\DimensionMetricFactory} in
 * `Dimension::configureMetrics()`.
 *
 * @api
 */
class ArchivedMetric extends Metric
{
    /**
     * Aggregation counting the rows with a non-NULL column value, to be used with the column name in place of `%s`.
     */
    public const AGGREGATION_COUNT = 'count(%s)';
    /**
     * Metric name prefix for count aggregations.
     *
     * @internal
     */
    public const AGGREGATION_COUNT_PREFIX = 'nb_';
    /**
     * Aggregation summing the column values.
     */
    public const AGGREGATION_SUM = 'sum(%s)';
    /**
     * Metric name prefix for sum aggregations.
     *
     * @internal
     */
    public const AGGREGATION_SUM_PREFIX = 'sum_';
    /**
     * Aggregation selecting the maximum column value.
     */
    public const AGGREGATION_MAX = 'max(%s)';
    /**
     * Metric name prefix for max aggregations.
     *
     * @internal
     */
    public const AGGREGATION_MAX_PREFIX = 'max_';
    /**
     * Aggregation selecting the minimum column value.
     */
    public const AGGREGATION_MIN = 'min(%s)';
    /**
     * Metric name prefix for min aggregations.
     *
     * @internal
     */
    public const AGGREGATION_MIN_PREFIX = 'min_';
    /**
     * Aggregation counting the distinct column values.
     */
    public const AGGREGATION_UNIQUE = 'count(distinct %s)';
    /**
     * Metric name prefix for unique count aggregations.
     *
     * @internal
     */
    public const AGGREGATION_UNIQUE_PREFIX = 'nb_uniq_';
    /**
     * Aggregation counting the rows where the column value is greater than zero.
     */
    public const AGGREGATION_COUNT_WITH_NUMERIC_VALUE = 'sum(if(%s > 0, 1, 0))';
    /**
     * Metric name prefix for aggregations counting rows with a numeric value.
     *
     * @internal
     */
    public const AGGREGATION_COUNT_WITH_NUMERIC_VALUE_PREFIX = 'nb_with_';

    /**
     * @var string|false
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

    /**
     * @internal
     */
    public function __construct(Dimension $dimension, $aggregation = false)
    {
        if (!empty($aggregation) && !str_contains($aggregation, '%s')) {
            throw new \Exception(sprintf('The given aggregation for %s.%s needs to include a %%s for the column name', $dimension->getDbTableName(), $dimension->getColumnName()));
        }

        $this->setDimension($dimension);
        $this->setDbTable($dimension->getDbTableName());
        $this->aggregation = $aggregation;
    }

    /**
     * Returns the SQL aggregation template of the metric.
     *
     * @return string|false The template containing `%s` for the column name, or `false` if there is none.
     *
     * @internal
     */
    public function getAggregation()
    {
        return $this->aggregation;
    }

    /**
     * Sets the dimension the metric is calculated from.
     *
     * @param Dimension $dimension The dimension.
     * @return $this
     *
     * @internal
     */
    public function setDimension($dimension)
    {
        $this->dimension = $dimension;
        return $this;
    }

    /**
     * Returns the dimension the metric is calculated from.
     *
     * @internal
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * Sets the ID of the category the metric belongs to.
     *
     * @param string $category The category ID.
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Returns the ID of the category the metric belongs to.
     *
     * @return string The category ID.
     */
    public function getCategoryId()
    {
        return $this->category;
    }

    /**
     * Sets the name of the database table the metric is queried from.
     *
     * @param string $dbTable The table name.
     * @return $this
     *
     * @internal
     */
    public function setDbTable($dbTable)
    {
        $this->dbTable = $dbTable;
        return $this;
    }

    /**
     * Sets the documentation text shown for the metric.
     *
     * @param string $documentation The translated documentation.
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * Sets the translated name of the metric, or a translation key.
     *
     * @param string $name The name or translation key.
     * @return $this
     */
    public function setTranslatedName($name)
    {
        $this->translatedName = $name;
        return $this;
    }

    /**
     * Sets the data type of the metric.
     *
     * @param string $type One of the `Dimension::TYPE_*` constants.
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Sets the name of the metric.
     *
     * @param string $name The metric name.
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets a custom SQL expression for the metric, replacing the one derived from the dimension and aggregation.
     *
     * @param string $query The SQL expression.
     * @return $this
     *
     * @internal
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Returns the name of the metric.
     *
     * @return string The metric name.
     */
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

    /**
     * Returns the data type of the metric.
     *
     * @return string One of the `Dimension::TYPE_*` constants, or an empty string if none was set.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the translated name of the metric.
     *
     * @return string The translated name, or an empty string if none was set.
     */
    public function getTranslatedName()
    {
        if (!empty($this->translatedName)) {
            return Piwik::translate($this->translatedName);
        }

        return $this->translatedName;
    }

    /**
     * @internal
     */
    public function getDependentMetrics()
    {
        return array($this->getName());
    }

    /**
     * Returns the documentation text of the metric.
     *
     * @return string The documentation text.
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Returns the name of the database table the metric is queried from.
     *
     * @return string The table name.
     *
     * @internal
     */
    public function getDbTableName()
    {
        return $this->dbTable;
    }

    /**
     * Returns the SQL expression used to calculate the metric.
     *
     * @return string The SQL expression.
     *
     * @internal
     */
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
