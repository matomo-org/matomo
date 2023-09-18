<?php

namespace Piwik\Tests\Framework\Mock;

use Piwik\SiteContentDetector;

class FakeSiteContentDetector extends SiteContentDetector
{
    public function __construct($detectedContentDetections = [], $connectedConsentManagers = [])
    {
        foreach ($detectedContentDetections as $detectedContentDetection) {
            $class = $this->getSiteContentDetectionById($detectedContentDetection);
            $this->detectedContent[$class::getContentType()][$detectedContentDetection] = true;
        }

        $this->connectedConsentManagers  = $connectedConsentManagers;
        parent::__construct();
    }

    public function detectContent(array $detectContent = [],
                                  ?int $idSite = null, ?array $siteResponse = null, int $timeOut = 60): void
    {
        // skip any detections
    }
}
