<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Piwik;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRegistry;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\UserPromotionState;
use Piwik\Plugins\ProfessionalServices\Widgets\DismissibleWidget;
use Piwik\Request;

/**
 * Provides API methods for Professional Services widgets and prompts.
 *
 * @method static \Piwik\Plugins\ProfessionalServices\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private PromoWidgetDismissal $promoWidgetDismissal;

    private PromotionRegistry $promotionRegistry;

    private UserPromotionState $userPromotionState;

    public function __construct(
        PromoWidgetDismissal $promoWidgetDismissal,
        PromotionRegistry $promotionRegistry,
        UserPromotionState $userPromotionState
    ) {
        $this->promoWidgetDismissal = $promoWidgetDismissal;
        $this->promotionRegistry = $promotionRegistry;
        $this->userPromotionState = $userPromotionState;
    }

    /**
     * Dismisses a Professional Services promo widget for the current user.
     *
     * @internal
     * @return bool Returns `true` when the widget dismissal was recorded.
     */
    public function dismissWidget(): bool
    {
        Piwik::checkUserIsNotAnonymous();

        $widgetName = Request::fromRequest()->getStringParameter('widgetName');

        if (!DismissibleWidget::exists($widgetName)) {
            throw new \Exception('Can\'t dismiss unknown widget ' . $widgetName);
        }

        $this->promoWidgetDismissal->dismissPromoWidget($widgetName);

        return true;
    }

    /**
     * Dismisses the contextual plugin promotion shown on the dashboard for the current
     * user.
     *
     * Dismissing starts a short cooldown on all triggered promotions and a long cooldown
     * on this plugin, both for this user only and across every website they can access.
     *
     * @internal
     * @return bool Returns `true` when the dismissal was recorded.
     */
    public function dismissDashboardPromotion(): bool
    {
        Piwik::checkUserIsNotAnonymous();

        $request = Request::fromRequest();
        $pluginName = $request->getStringParameter('pluginName');
        $triggerName = $request->getStringParameter('triggerName');

        if (null === $this->promotionRegistry->findByPluginAndTrigger($pluginName, $triggerName)) {
            throw new \Exception('Can\'t dismiss unknown plugin promotion ' . $pluginName);
        }

        $this->userPromotionState->dismiss($pluginName, $triggerName);

        return true;
    }
}
