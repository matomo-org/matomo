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

    public function getSegment()
    {
        return $this->segment;
    }

    public function setSegment(Piwik_Segment $segment)
    {
        $this->segment = $segment;
    }

    public function getPeriods()
    {
        return $this->periods;
    }

    public function setPeriods($periods)
    {
        $this->periods = $this->getAsNonEmptyArray($periods, 'periods');
    }

    public function getIdSites()
    {
        return $this->idSites;
    }

    public function setIdSites($idSites)
    {
        $this->idSites = $this->getAsNonEmptyArray($idSites, 'idSites');
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

