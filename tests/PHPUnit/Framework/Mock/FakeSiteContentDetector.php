<?php

namespace Piwik\Tests\Framework\Mock;

use Piwik\SiteContentDetector;

class FakeSiteContentDetector extends SiteContentDetector
{

    private $mockData;

    public function __construct($mockData = [])
    {
        $this->mockData = $mockData;
    }

    public function detectContent(array $detectContent = [SiteContentDetector::ALL_CONTENT],
                                  ?int $idSite = null, ?string $siteData = null, int $timeOut = 60)
    {

        foreach ($this->mockData as $property => $value) {
            if (property_exists($this, $property)) {
               $this->{$property} = $value;
            }
        }

        return true;
    }

}
