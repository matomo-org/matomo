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
use Piwik\SiteContentDetector;


class ChallengeSetupConsentManager extends Challenge
{

    /** @var SiteContentDetector */
    private $siteContentDetector;


    /**
     * @param SiteContentDetector $siteContentDetector
     * @param array|null         $siteData    String of site content, content of the current site will be retrieved if left blank
     */
    public function __construct(SiteContentDetector $siteContentDetector, ?array $siteData = null)
    {
        parent::__construct();
        $this->siteContentDetector = $siteContentDetector;
        $this->siteContentDetector->detectContent([SiteContentDetector::CONSENT_MANAGER], null, $siteData);
    }

    public function getName()
    {
        return Piwik::translate('Tour_ConnectConsentManager', [$this->siteContentDetector->consentManagerName]);
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_ConnectConsentManagerIntro', [$this->siteContentDetector->consentManagerName]);
    }

    public function getId()
    {
        return 'setup_consent_manager';
    }

    public function getConsentManagerId()
    {
        return $this->siteContentDetector->consentManagerId;
    }

    public function isCompleted()
    {

        if (!$this->siteContentDetector->consentManagerId) {
            return true;
        }

        return $this->siteContentDetector->isConnected;
    }

    public function isDisabled()
    {
        return ($this->siteContentDetector->consentManagerId === null);
    }

    public function getUrl()
    {
        if ($this->siteContentDetector->consentManagerId === null) {
            return '';
        }

        return $this->siteContentDetector->consentManagerUrl;
    }

}
