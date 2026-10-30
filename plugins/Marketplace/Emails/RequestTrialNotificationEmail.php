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
use Piwik\Plugins\Marketplace\PluginTrial\UnsubscribeToken;
use Piwik\SettingsPiwik;
use Piwik\View;

class RequestTrialNotificationEmail extends Mail
{
    private string $emailAddress;

    private string $login;

    private ?string $unsubscribeToken;

    private string $pluginName;
    private string $pluginDisplayName;

    /**
     * @param string|null $unsubscribeToken null to sign one for `$login`; if there is no such user, the email
     *                                      has no unsubscribe line
     */
    public function __construct(
        string $login,
        string $emailAddress,
        string $pluginName,
        string $pluginDisplayName,
        ?string $unsubscribeToken = null
    ) {
        parent::__construct();

        $this->emailAddress = $emailAddress;
        $this->login = $login;
        $this->unsubscribeToken = $unsubscribeToken
            ?? StaticContainer::get(UnsubscribeToken::class)->generate($login);
        $this->pluginName = $pluginName;
        $this->pluginDisplayName = $pluginDisplayName;

        $this->setUpEmail();
    }

    protected function getDefaultSubject(): string
    {
        return Piwik::translate(
            'Marketplace_RequestTrialNotificationEmailSubject',
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

    /**
     * @return string|null null when there is no user to sign a token for, so the footer leaves the line out
     */
    private function getUnsubscribeLink(): ?string
    {
        if ($this->unsubscribeToken === null) {
            return null;
        }

        return SettingsPiwik::getPiwikUrl() . 'index.php?' . http_build_query([
            'module' => 'Marketplace',
            'action' => 'unsubscribeTrialRequestEmails',
            'login' => $this->login,
            'token' => $this->unsubscribeToken,
        ]);
    }
}
