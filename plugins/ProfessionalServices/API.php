<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Container\StaticContainer;
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

    public function __construct(PromoWidgetDismissal $promoWidgetDismissal)
    {
        $this->promoWidgetDismissal = $promoWidgetDismissal;
    }

    /**
     * The promotion services are resolved when a promotion method is actually called,
     * rather than injected.
     *
     * Constructing the registry pulls in all 22 triggers, and through them the Marketplace
     * plugin. Injected, that would make this whole API class - `dismissWidget()` included,
     * which predates the promotions - impossible to construct wherever Marketplace is not
     * available. The one method that needs them asks for them instead.
     *
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    private function get(string $className)
    {
        return StaticContainer::get($className);
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
     * @param string $pluginName  Name of the promoted plugin as the Marketplace knows it,
     *                            eg. `CustomReports`. Must name a registered promotion
     *                            together with `$triggerName`.
     * @param string $triggerName Name of the trigger the promotion was shown for, eg.
     *                            `segments`. A plugin can be promoted by more than one
     *                            trigger, and dismissing any of them silences the plugin.
     * @return bool Returns `true` when the dismissal was recorded.
     * @throws \Exception If the plugin and trigger do not name a registered promotion.
     */
    public function dismissDashboardPromotion(string $pluginName, string $triggerName): bool
    {
        Piwik::checkUserIsNotAnonymous();

        if (null === $this->get(PromotionRegistry::class)->findByPluginAndTrigger($pluginName, $triggerName)) {
            throw new \Exception('Can\'t dismiss unknown plugin promotion ' . $pluginName);
        }

        $this->get(UserPromotionState::class)->dismiss($pluginName, $triggerName);

        return true;
    }
}
