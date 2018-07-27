<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CronArchive\Performance;

class Measurement implements \JsonSerializable
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $measuredName;

    /**
     * @var string
     */
    private $idSite;

    /**
     * @var string
     */
    private $dateRange;

    /**
     * @var string
     */
    private $periodType;

    /**
     * @var string
     */
    private $segment;

    /**
     * @var float
     */
    private $time;

    /**
     * @var int
     */
    private $memory;

    public function __construct($category, $name, $idSite, $dateRange, $periodType, $segment, $time, $memory)
    {
        $this->category = $category;
        $this->measuredName = $name;
        $this->idSite = $idSite;
        $this->dateRange = $dateRange;
        $this->periodType = $periodType;
        $this->segment = trim($segment);
        $this->time = $time;
        $this->memory = $memory;
    }

    public function __toString()
    {
        $parts = [
            ucfirst($this->category) . ": {$this->measuredName}",
            "idSite: {$this->idSite}",
            "period: {$this->periodType} ({$this->dateRange})",
            "segment: " . (!empty($this->segment) ? $this->segment : 'none'),
            "duration: {$this->time}s",
            "memory leak: {$this->memory}",
        ];

        return implode(', ', $parts);
    }

    public function jsonSerialize()
    {
        return [
            'category' => $this->category,
            'name' => $this->measuredName,
            'idSite' => $this->idSite,
            'date_range' => $this->dateRange,
            'period' => $this->periodType,
            'segment' => $this->segment,
            'time' => $this->time,
            'memory' => $this->memory,
        ];
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getMeasuredName()
    {
        return $this->measuredName;
    }

    /**
     * @param string $measuredName
     */
    public function setMeasuredName($measuredName)
    {
        $this->measuredName = $measuredName;
    }

    /**
     * @return string
     */
    public function getIdSite()
    {
        return $this->idSite;
    }

    /**
     * @param string $idSite
     */
    public function setIdSite($idSite)
    {
        $this->idSite = $idSite;
    }

    /**
     * @return string
     */
    public function getDateRange()
    {
        return $this->dateRange;
    }

    /**
     * @param string $dateRange
     */
    public function setDateRange($dateRange)
    {
        $this->dateRange = $dateRange;
    }

    /**
     * @return string
     */
    public function getPeriodType()
    {
        return $this->periodType;
    }

    /**
     * @param string $periodType
     */
    public function setPeriodType($periodType)
    {
        $this->periodType = $periodType;
    }

    public static function fromArray(array $data)
    {
        return new Measurement($data['category'], $data['name'], $data['idSite'], $data['date_range'], $data['period'],
            $data['segment'], $data['time'], $data['memory']);
    }
}