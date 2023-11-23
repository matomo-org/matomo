<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Option;
use Piwik\Piwik;

class PromoWidgetDismissal
{
    private const DISMISSED_WIDGET_OPTION_NAME = 'ProfessionalServices.DismissedWidget.%s.%s';

    public function dismissPromoWidget(string $widgetName): void
    {
        Option::set($this->getDismissedWidgetOptionName($widgetName), time());
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
        return Option::get($this->getDismissedWidgetOptionName($widgetName)) > 0;
    }

    private function getDismissedWidgetOptionName(string $widgetName): string
    {
        return sprintf(self::DISMISSED_WIDGET_OPTION_NAME, $widgetName, Piwik::getCurrentUserLogin());
    }
}
