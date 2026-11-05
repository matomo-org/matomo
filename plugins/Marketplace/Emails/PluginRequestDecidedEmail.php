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
use Piwik\SettingsPiwik;
use Piwik\View;

/**
 * Tells a requester that a Super User, or Matomo itself, approved or declined their plugin request
 */
class PluginRequestDecidedEmail extends Mail
{
    /**
     * @param string $decision one of the RequestHistory::DECISION_* constants
     * @param string|null $note the Super User's note, only shown for a declined request
     */
    public function __construct(
        private string $login,
        private string $emailAddress,
        private string $pluginName,
        private string $pluginDisplayName,
        private string $decision,
        private ?string $note = null
    ) {
        parent::__construct();

        $this->setUpEmail();
    }

    private function isApproved(): bool
    {
        return $this->decision === RequestHistory::DECISION_APPROVED;
    }

    protected function getDefaultSubject(): string
    {
        return Piwik::translate(
            $this->isApproved() ? 'Marketplace_PluginRequestApprovedEmailSubject' : 'Marketplace_PluginRequestDeclinedEmailSubject',
            [$this->pluginDisplayName]
        );
    }

    protected function getDefaultBodyView(): View
    {
        $piwikUrl = SettingsPiwik::getPiwikUrl();
        $view = new View('@Marketplace/_pluginRequestDecidedEmail.twig');

        $view->login = $this->login;
        $view->pluginName = $this->pluginDisplayName;
        $view->isApproved = $this->isApproved();
        $view->note = $this->isApproved() ? null : $this->note;

        // @see JavaScript broadcast#propagateNewPopoverParameter
        $view->pluginLink = $piwikUrl . 'index.php?module=Marketplace&action=overview#?showPlugin=' . $this->pluginName;

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
            // the opt-out is a personal setting, so the link needs no token: it only works once logged in
            SettingsPiwik::getPiwikUrl() . 'index.php?module=UsersManager&action=userSettings',
            'Marketplace_PluginRequestDecidedEmailFooter'
        ));
    }
}
