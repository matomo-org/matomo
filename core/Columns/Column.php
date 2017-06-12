<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;
use Piwik\Piwik;
use Piwik\Plugin\Segment;

/**
 * @api
 * @since 3.1.0
 */
abstract class Column extends Dimension
{
    /**
     * Segment type 'dimension'. Can be used along with {@link setType()}.
     * @api
     */
    const TYPE_DIMENSION = 'dimension';
    const TYPE_MONEY = 'money';
    const TYPE_DURATION_MS = 'duration_ms';
    const TYPE_DURATION_S = 'duration_s';
    const TYPE_NUMBER = 'number';
    const TYPE_FLOAT = 'float';

    protected $type = self::TYPE_DIMENSION;

    /**
     * Translation key for name
     * @var string
     */
    protected $name = '';

    /**
     * Translation key for category
     * @var string
     */
    protected $category = '';
    protected $segmentName = '';
    protected $suggestedValuesCallback;
    protected $acceptValues;
    protected $sqlFilter;
    protected $sqlFilterValue;
    protected $allowAnonymous;

    public function getCategory()
    {
        if (!empty($this->category)) {
            return Piwik::translate($this->category);
        }

        return $this->category;
    }

    public function getName()
    {
        if (!empty($this->name)) {
            return Piwik::translate($this->name);
        }

        return $this->name;
    }

    protected function configureSegments()
    {
        if ($this->segmentName && $this->category && $this->columnName && $this->dbTableName && $this->name) {
            $segment = new Segment();
            $segment->setSegment($this->segmentName);
            $segment->setCategory($this->category);
            $segment->setName($this->name);
            $segment->setSqlSegment($this->dbTableName . '.' . $this->columnName);

            $this->addSegment($segment);
        }
    }

    /**
     * Adds a new segment. It automatically sets the SQL segment depending on the column name in case none is set
     * already.
     * @see \Piwik\Columns\Column::addSegment()
     * @param Segment $segment
     * @api
     */
    protected function addSegment(Segment $segment)
    {
        if (!$segment->getType() && $this->getType() == self::TYPE_DIMENSION) {
            $segment->setType(Segment::TYPE_DIMENSION);
        } elseif (!$segment->getType()) {
            $segment->setType(Segment::TYPE_METRIC);
        }

        $sqlSegment = $segment->getSqlSegment();
        if (!empty($this->columnName) && empty($sqlSegment)) {
            $segment->setSqlSegment($this->dbTableName . '.' . $this->columnName);
        }

        if ($this->acceptValues) {
            $segment->setAcceptedValues($this->acceptValues);
        }

        if ($this->sqlFilterValue) {
            $segment->setSqlFilterValue($this->sqlFilterValue);
        }

        if ($this->sqlFilter) {
            $segment->setSqlFilter($this->sqlFilter);
        }

        if (!$this->allowAnonymous) {
            $segment->setRequiresAtLeastViewAccess(true);
        }

        parent::addSegment($segment);
    }

    public function getDbTableName()
    {
        return $this->dbTableName;
    }

    /**
     * TODO in Piwik 4 rename to getColumnType, rename getColumnType to getDbColumnType
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the version of the dimension which is used for update checks.
     * @return string
     * @ignore
     */
    public function getVersion()
    {
        return $this->columnType;
    }

}
