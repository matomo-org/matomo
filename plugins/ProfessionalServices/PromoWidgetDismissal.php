<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Piwik;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;

class PromoWidgetDismissal
{
    private const STORE_KEY_DISMISSED_WIDGETS = 'dismissedWidgets';

    /** @var UserScopedSettingsAccessManager */
    private $accessManager;

    public function __construct(UserScopedSettingsAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    public function dismissPromoWidget(string $widgetName): void
    {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return;
        }

        $dismissedWidgets = $this->getDismissedWidgets($userLogin);
        $dismissedWidgets[$widgetName] = time();
        $this->accessManager->set('ProfessionalServices', $userLogin, self::STORE_KEY_DISMISSED_WIDGETS, $dismissedWidgets);
    }

    public function isPromoWidgetDismissedForCurrentUser(string $widgetName): bool
    {
        $isAnonUser = Piwik::isUserIsAnonymous();

        if ($isAnonUser) {
            return false;
        }

        return $this->isPromoWidgetDismissed($widgetName);
    }

    private function isPromoWidgetDismissed(string $widgetName): bool
    {
        $userLogin = Piwik::getCurrentUserLogin();
        if (empty($userLogin)) {
            return false;
        }

        $dismissedWidgets = $this->getDismissedWidgets($userLogin);
        return !empty($dismissedWidgets[$widgetName]) && $dismissedWidgets[$widgetName] > 0;
    }

    private function getDismissedWidgets(string $userLogin): array
    {
        $value = $this->accessManager->get('ProfessionalServices', $userLogin, self::STORE_KEY_DISMISSED_WIDGETS, []);
        if (is_array($value)) {
            return $value;
        }

        return [];
    }
}
