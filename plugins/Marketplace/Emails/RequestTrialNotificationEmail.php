<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Marketplace\Emails;

use Piwik\Container\StaticContainer;
use Piwik\Email\ContentGenerator;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\UnsubscribeToken;
use Piwik\SettingsPiwik;
use Piwik\View;

class RequestTrialNotificationEmail extends Mail
{
    private string $emailAddress;

    private string $login;

    private string $pluginName;
    private string $pluginDisplayName;

    private bool $isInstallRequest;

    /**
     * @param string $requestType one of the RequestHistory::REQUEST_TYPE_* constants
     */
    public function __construct(
        string $login,
        string $emailAddress,
        string $pluginName,
        string $pluginDisplayName,
        string $requestType = RequestHistory::REQUEST_TYPE_TRIAL
    ) {
        parent::__construct();

        $this->isInstallRequest = $requestType === RequestHistory::REQUEST_TYPE_INSTALL;

        $this->emailAddress = $emailAddress;
        $this->login = $login;
        $this->pluginName = $pluginName;
        $this->pluginDisplayName = $pluginDisplayName;

        $this->setUpEmail();
    }

    protected function getDefaultSubject(): string
    {
        return Piwik::translate(
            $this->isInstallRequest
                ? 'Marketplace_RequestInstallNotificationEmailSubject'
                : 'Marketplace_RequestTrialNotificationEmailSubject',
            [
                $this->pluginDisplayName,
            ]
        );
    }

    protected function getDefaultBodyView(): View
    {
        $piwikUrl = SettingsPiwik::getPiwikUrl();
        $view = new View('@Marketplace/_requestTrialNotificationEmail.twig');

        $view->login = $this->login;
        $view->isInstallRequest = $this->isInstallRequest;
        $view->marketplaceLink = $piwikUrl . 'index.php?module=Marketplace&action=overview';
        $view->pluginName = $this->pluginDisplayName;

        // @see JavaScript broadcast#propagateNewPopoverParameter
        $view->pluginLink =
            $piwikUrl
            . 'index.php?module=Marketplace&action=overview#?showPlugin=' . $this->pluginName;

        return $view;
    }

    private function setUpEmail(): void
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->emailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setBodyHtml(StaticContainer::get(ContentGenerator::class)->generateHtmlContent(
            $this->getDefaultBodyView(),
            $this->getUnsubscribeLink(),
            'Marketplace_RequestTrialUnsubscribeFooter'
        ));
    }

    private function getUnsubscribeLink(): string
    {
        return SettingsPiwik::getPiwikUrl() . 'index.php?' . http_build_query([
            'module' => 'Marketplace',
            'action' => 'unsubscribeTrialRequestEmails',
            'login' => $this->login,
            'token' => StaticContainer::get(UnsubscribeToken::class)->generate($this->login),
        ]);
    }
}
