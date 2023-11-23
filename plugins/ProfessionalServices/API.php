<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Piwik;
use Piwik\Plugins\ProfessionalServices\Widgets\DismissibleWidget;

class API extends \Piwik\Plugin\API
{
    /**
     * @var PromoWidgetDismissal
     */
    private $promoWidgetDismissal;

    public function __construct(PromoWidgetDismissal $promoWidgetDismissal)
    {
        $this->promoWidgetDismissal = $promoWidgetDismissal;
    }

    /**
     * Dismisses a promo widget to no longer be shown in the menu
     *
     * @internal
     *
     * @param string $widgetName
     * @return bool
     * @throws \Piwik\NoAccessException
     */
    public function dismissWidget(string $widgetName): bool
    {
        Piwik::checkUserIsNotAnonymous();

        if (!DismissibleWidget::exists($widgetName)) {
            throw new \Exception('Can\'t dismiss unknown widget ' . $widgetName);
        }

        $this->promoWidgetDismissal->dismissPromoWidget($widgetName);

        return true;
    }
}
