<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;
use Piwik\SiteContentDetector;
use Piwik\Url;
use Piwik\View;

class WordPress extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'WordPress';
    }

    public static function getIcon(): string
    {
        return './plugins/SitesManager/images/wordpress.svg';
    }

    public static function getContentType(): int
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/');
    }

    public static function getPriority(): int
    {
        return 30;
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $needle = '/wp-content';
        return (strpos($data, $needle) !== false);
    }

    public function renderInstructionsTab(SiteContentDetector $detector): string
    {
        $view     = new View("@SitesManager/_wordpressTabInstructions");
        $faqLink  = Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/general/faq_114/');
        $authLink = '';
        if (Piwik::isUserHasSomeViewAccess()) {
            $request  = \Piwik\Request::fromRequest();
            $idSite   = $request->getIntegerParameter('idSite', 0);
            $period   = $request->getStringParameter('period', 'day');
            $date     = $request->getStringParameter('date', 'yesterday');
            $authLink = SettingsPiwik::getPiwikUrl() . 'index.php?' .
                Url::getQueryStringFromParameters([
                                                      'idSite' => $idSite,
                                                      'date'   => $date,
                                                      'period' => $period,
                                                      'module' => 'UsersManager',
                                                      'action' => 'addNewToken',
                                                  ]);
        }
        $view->authLink = $authLink;
        $view->faqLink  = $faqLink;
        $view->sendHeadersWhenRendering = false;
        $view->site = ['id' => $idSite, 'name' => ''];
        $view->isJsTrackerInstallCheckAvailable = Manager::getInstance()->isPluginActivated('JsTrackerInstallCheck');
        return $view->render();
    }

    public function renderOthersInstruction(SiteContentDetector $detector): string
    {
        if ($detector->wasDetected(self::getId())) {
            return ''; // don't show on others page if tab is being displayed
        }

        return sprintf(
            '<p>%s</p>',
            Piwik::translate(
                'SitesManager_SiteWithoutDataWordpressDescription',
                [
                    '<a target="_blank" rel="noreferrer noopener" href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/') . '">',
                    '</a>',
                ]
            )
        );
    }
}
