<?php

namespace Piwik\Tests\Framework\Mock;

use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\SiteContentDetector;

class FakeSiteContentDetector extends SiteContentDetector
{

    /**
     * @var SiteContentDetectionAbstract[]
     */
    private $detectedContentDetections;

    public function __construct($detectedContentDetections = [], $connectedConsentManagers = [])
    {
        $this->detectedContentDetections = $detectedContentDetections;
        $this->connectedConsentManagers  = $connectedConsentManagers;
        parent::__construct(null);
    }

    public function detectContent(array $detectContent = [SiteContentDetector::ALL_CONTENT],
                                  ?int $idSite = null, ?array $siteResponse = null, int $timeOut = 60): void
    {
        // skip any detections
    }

    public function wasDetected(string $detectionClassId): bool
    {
        return in_array($detectionClassId, $this->detectedContentDetections);
    }

    public function getDetectsByType(string $type): array
    {
        $result = [];

        foreach ($this->detectedContentDetections as $detectedContentDetection) {
            $class = $this->getSiteContentDetectionById($detectedContentDetection);
            if ($class::getContentType() === $type) {
                $result[] = $detectedContentDetection;
            }
        }

        return $result;
    }
}
