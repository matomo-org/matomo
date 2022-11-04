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

    private $consentManager = null;


    /**
     * @param string|null $siteData    String of site content, content of the current site will be retrieved if left blank
     */
    public function __construct(?string $siteData = null)
    {
        parent::__construct();
        $this->consentManager = SiteContentDetector::getInstance();
        $this->consentManager->detectContent([SiteContentDetector::CONSENT_MANAGER], null, $siteData);
    }

    public function getName()
    {
        return Piwik::translate('Tour_ConnectConsentManager', [$this->consentManager->consentManagerName]);
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_ConnectConsentManagerIntro', [$this->consentManager->consentManagerName]);
    }

    public function getId()
    {
        return 'setup_consent_manager';
    }

    public function getConsentManagerId()
    {
        return $this->consentManager->consentManagerId;
    }

    public function isCompleted()
    {

        if (!$this->consentManager->consentManagerId) {
            return true;
        }

        return $this->consentManager->isConnected;
    }

    public function isDisabled()
    {
        return ($this->consentManager->consentManagerId === null);
    }

    public function getUrl()
    {
        if ($this->consentManager->consentManagerId === null) {
            return '';
        }

        return $this->consentManager->consentManagerUrl;
    }

}