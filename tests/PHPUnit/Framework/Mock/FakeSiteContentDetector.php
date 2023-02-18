<?php

namespace Piwik\Tests\Framework\Mock;

use Piwik\SiteContentDetector;

class FakeSiteContentDetector extends SiteContentDetector
{

    private $mockData;

    public function __construct($mockData = [])
    {
        $this->mockData = $mockData;
        parent::__construct(null);
    }

    public function detectContent(array $detectContent = [SiteContentDetector::ALL_CONTENT],
                                  ?int $idSite = null, ?array $siteResponse = null, int $timeOut = 60): void
    {
        foreach ($this->mockData as $property => $value) {
            if (property_exists($this, $property)) {
               $this->{$property} = $value;
            }
        }
    }

}
