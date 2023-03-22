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
use Piwik\Columns\MetricsList;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;

class ComputedMetric extends ProcessedMetric
{
    const AGGREGATION_AVG = 'avg';
    const AGGREGATION_RATE = 'rate';

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
    private $metric1 = '';
    private $metric2 = '';
    private $category = '';

    private $metricsList;

    public function __construct($metric1, $metric2, $aggregation = 'avg')
    {
        $nameShort1 = str_replace(array('nb_'), '', $metric1);
        $nameShort2 = str_replace(array('nb_'), '', $metric2);

        if ($aggregation === ComputedMetric::AGGREGATION_AVG) {
            $this->name = 'avg_' . $nameShort1 . '_per_' . $nameShort2;
        } elseif ($aggregation === ComputedMetric::AGGREGATION_RATE) {
            $this->name = $nameShort1 . '_' . $nameShort2 . '_rate';
        } else {
            throw new \Exception('Not supported aggregation type');
        }

        $this->setMetric1($metric1);
        $this->setMetric2($metric2);
        $this->aggregation = $aggregation;
    }

    public function getDependentMetrics()
    {
        return array($this->metric1, $this->metric2);
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

    public function setMetric1($metric1)
    {
        $this->metric1 = $metric1;
        return $this;
    }

    public function setMetric2($metric2)
    {
        $this->metric2 = $metric2;
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

    public function getName()
    {
        return $this->name;
    }

    public function compute(Row $row)
    {
        $metric1 = $this->getMetric($row, $this->metric1);
        $metric2 = $this->getMetric($row, $this->metric2);

        $precision = 2;
        if ($this->aggregation === self::AGGREGATION_RATE) {
            $precision = 3;
        }

        return Piwik::getQuotientSafe($metric1, $metric2, $precision);
    }

    private function getDetectedType()
    {
        if (!$this->type) {
            if ($this->aggregation === self::AGGREGATION_RATE) {
                $this->type = Dimension::TYPE_PERCENT;
            } else {
                $this->type = Dimension::TYPE_NUMBER; // default to number
                $metric1 = $this->getMetricsList()->getMetric($this->metric1);
                if ($metric1) {
                    $dimension = $metric1->getDimension();
                    if ($dimension && $dimension->getType() != Dimension::TYPE_BOOL) {
                        $this->type = $dimension->getType();
                    }
                }
            }
        }

        return $this->type;
    }

    public function format($value, Formatter $formatter)
    {
        if ($this->aggregation === self::AGGREGATION_RATE) {
            return $formatter->getPrettyPercentFromQuotient($value);
        }

        $type = $this->getDetectedType();

        switch ($type) {
            case Dimension::TYPE_MONEY:
                return $formatter->getPrettyMoney($value, $this->idSite);
            case Dimension::TYPE_FLOAT:
                return $formatter->getPrettyNumber($value, $precision = 2);
            case Dimension::TYPE_NUMBER:
                return $formatter->getPrettyNumber($value, 1); // we still need to round to have somewhat more accurate result
            case Dimension::TYPE_DURATION_S:
                return $formatter->getPrettyTimeFromSeconds(round($value), $displayAsSentence = true);
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

    public function getTranslatedName()
    {
        if (!$this->translatedName) {
            $metric = $this->getMetricsList();
            $metric1 = $metric->getMetric($this->metric1);
            $metric2 = $metric->getMetric($this->metric2);

            if ($this->aggregation === self::AGGREGATION_AVG) {
                if ($metric1 && $metric1 instanceof ArchivedMetric && $metric2 && $metric2 instanceof ArchivedMetric) {

                    $metric1Name = $metric1->getDimension()->getName();
                    $metric2Name = $metric2->getDimension()->getName();
                    return Piwik::translate('General_ComputedMetricAverage', array($metric1Name, $metric2Name));
                }

                if ($metric1 && $metric1 instanceof ArchivedMetric) {
                    $metric1Name = $metric1->getDimension()->getName();
                    return Piwik::translate('General_AverageX', array($metric1Name));
                }

                if ($metric1 && $metric2) {
                    return $metric1->getTranslatedName() . ' per ' . $metric2->getTranslatedName();
                }

                return $this->metric1 . ' per ' . $this->metric2;
            } else if ($this->aggregation === self::AGGREGATION_RATE) {
                if ($metric1 && $metric1 instanceof ArchivedMetric) {
                    return Piwik::translate('General_ComputedMetricRate', array($metric1->getTranslatedName()));
                } else {
                    return Piwik::translate('General_ComputedMetricRate', array($this->metric1));
                }
            }
        }
        return $this->translatedName;
    }

    public function getDocumentation()
    {
        if (!$this->documentation) {
            $metric = $this->getMetricsList();
            $metric1 = $metric->getMetric($this->metric1);
            $metric2 = $metric->getMetric($this->metric2);

            if ($this->aggregation === self::AGGREGATION_AVG) {
                if ($metric1 && $metric1 instanceof ArchivedMetric && $metric2 && $metric2 instanceof ArchivedMetric) {
                    return Piwik::translate('General_ComputedMetricAverageDocumentation', array($metric1->getDimension()->getName(), $metric2->getTranslatedName()));
                }

                if ($metric1 && $metric1 instanceof ArchivedMetric) {
                    return Piwik::translate('General_ComputedMetricAverageShortDocumentation', array($metric1->getDimension()->getName()));
                }

                return Piwik::translate('General_ComputedMetricAverageDocumentation', array($this->metric1, $this->metric2));

            } else if ($this->aggregation === self::AGGREGATION_RATE) {
                if ($metric1 && $metric1 instanceof ArchivedMetric) {
                    return Piwik::translate('General_ComputedMetricRateDocumentation', array($metric1->getDimension()->getNamePlural(), $metric2->getDimension()->getNamePlural()));
                } else {
                    return Piwik::translate('General_ComputedMetricRateShortDocumentation', array($this->metric1));
                }
            }
        }
        return $this->documentation;
    }

    private function getMetricsList()
    {
        if (!$this->metricsList) {
            $this->metricsList = MetricsList::get();
        }
        return $this->metricsList;
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
        return $this->getDetectedType();
    }
}
