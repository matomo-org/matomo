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
use Piwik\Request;

class API extends \Piwik\Plugin\API
{
    /**
     * Dismisses a promo widget to no longer be shown in the menu
     *
     * @internal
     *
     * @return bool
     * @throws \Piwik\NoAccessException
     */
    public function dismissWidget(): bool
    {
        Piwik::checkUserIsNotAnonymous();

        $widgetName = Request::fromRequest()->getStringParameter('widgetName');

        if (!DismissibleWidget::exists($widgetName)) {
            throw new \Exception('Can\'t dismiss unknown widget ' . $widgetName);
        }

        ProfessionalServices::dismissPromoWidget($widgetName);

        return true;
    }
}
