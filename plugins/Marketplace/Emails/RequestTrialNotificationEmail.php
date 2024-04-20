<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Marketplace\Emails;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\View;

class RequestTrialNotificationEmail extends Mail
{
    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $pluginName;

    public function __construct(string $login, string $emailAddress, string $pluginName)
    {
        parent::__construct();

        $this->emailAddress = $emailAddress;
        $this->login = $login;
        $this->pluginName = $pluginName;

        $this->setUpEmail();
    }

    /**
     * @return string
     */
    protected function getDefaultSubject(): string
    {
        return Piwik::translate(
            'Marketplace_RequestTrialNotificationEmailSubject',
            [
                $this->pluginName,
            ]
        );
    }

    /**
     * @return View
     */
    protected function getDefaultBodyView(): View
    {
        $piwikUrl = SettingsPiwik::getPiwikUrl();
        $view = new View('@Marketplace/_requestTrialNotificationEmail.twig');

        $view->login = $this->login;
        $view->marketplaceLink = $piwikUrl . 'index.php?module=Marketplace&action=overview';
        $view->pluginName = $this->pluginName;

        // @see JavaScript broadcast#propagateNewPopoverParameter
        $view->pluginLink =
            $piwikUrl
            . 'index.php?module=Marketplace&action=overview#?popover='
            . urlencode('browsePluginDetail$3A' . $this->pluginName);

        return $view;
    }

    private function setUpEmail(): void
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->emailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }
}
