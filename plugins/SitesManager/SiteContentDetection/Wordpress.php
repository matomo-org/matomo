<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

class Wordpress extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Wordpress';
    }

    public static function getContentType(): string
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/';
    }

    public static function getPriority(): int
    {
        return 30;
    }

    protected function detectSiteByContent(?string $data = null, ?array $headers = null): bool
    {
        $needle = '/wp-content';
        return (strpos($data, $needle) !== false);
    }

    public function shouldHighlightTabIfShown(): bool
    {
        return true;
    }

    public function renderInstructionsTab(array $detections = []): string
    {
        $view     = new View("@SitesManager/_wordpressTabInstructions");
        $faqLink  = 'https://matomo.org/faq/general/faq_114/';
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

    public function renderOthersInstruction(): string
    {
        return sprintf(
            '<p>%s</p>',
            Piwik::translate(
                'SitesManager_SiteWithoutDataWordpressDescription',
                [
                    '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/">',
                    '</a>',
                ]
            )
        );
    }
}
