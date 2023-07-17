<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SiteContentDetection\ConsentManagerDetectionAbstract;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\SiteContentDetector;


class ChallengeSetupConsentManager extends Challenge
{

    /** @var SiteContentDetector */
    private $siteContentDetector;

    /**
     * @var ConsentManagerDetectionAbstract|null
     */
    private $detectedContentManager;


    /**
     * @param SiteContentDetector $siteContentDetector
     * @param array|null         $siteData    String of site content, content of the current site will be retrieved if left blank
     */
    public function __construct(SiteContentDetector $siteContentDetector, ?array $siteData = null)
    {
        parent::__construct();
        $this->siteContentDetector = $siteContentDetector;
        $this->siteContentDetector->detectContent([SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER], null, $siteData);
        $contentManagers = $this->siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER);
        $this->detectedContentManager = $this->siteContentDetector->getSiteContentDetectionById(reset($contentManagers));
    }

    public function getName()
    {
        return Piwik::translate('Tour_ConnectConsentManager', [$this->getConsentManagerName()]);
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_ConnectConsentManagerIntro', [$this->getConsentManagerName()]);
    }

    public function getId()
    {
        return 'setup_consent_manager';
    }

    public function getConsentManagerId()
    {
        if (empty($this->detectedContentManager)) {
            return null;
        }

        return $this->detectedContentManager::getId();
    }

    public function getConsentManagerName()
    {
        if (empty($this->detectedContentManager)) {
            return '';
        }

        return $this->detectedContentManager::getName();
    }

    public function isCompleted(string $login)
    {
        if (empty($this->detectedContentManager)) {
            return true;
        }

        return in_array($this->detectedContentManager::getId(), $this->siteContentDetector->connectedConsentManagers);
    }

    public function isDisabled()
    {
        return empty($this->detectedContentManager);
    }

    public function getUrl()
    {
        if (empty($this->detectedContentManager)) {
            return '';
        }

        return $this->detectedContentManager::getInstructionUrl();
    }

}
