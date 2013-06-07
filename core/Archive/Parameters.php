<?php

class Piwik_Archive_Parameters
{
    /**
     * The list of site IDs to query archive data for.
     *
     * @var array
     */
    private $idSites = array();

    /**
     * The list of Piwik_Period's to query archive data for.
     *
     * @var array
     */
    private $periods = array();

    /**
     * Segment applied to the visits set.
     *
     * @var Piwik_Segment
     */
    private $segment;


    public function setIdSites($idSites)
    {
        $this->idSites = $this->getAsNonEmptyArray($idSites, 'idSites');
    }

    public function setPeriods($periods)
    {
        $periods = $this->getAsNonEmptyArray($periods, 'periods');
        foreach ($periods as $period) {
            $this->periods[$period->getRangeString()] = $period;
        }
    }

    public function getSegment()
    {
        return $this->segment;
    }

    public function getPeriods()
    {
        return $this->periods;
    }
    public function getIdSites()
    {
        return $this->idSites;
    }


    public function setSegment(Piwik_Segment $segment)
    {
        $this->segment = $segment;
    }

    private function getAsNonEmptyArray($array, $paramName)
    {
        if (!is_array($array)) {
            $array = array($array);
        }

        if (empty($array)) {
            throw new Exception("Piwik_Archive::__construct: \$$paramName is empty.");
        }

        return $array;
    }

}

