/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import {
  AjaxHelper,
  Matomo,
  MatomoUrl,
  NotificationsStore,
  ReportingMenuStore,
  translate,
} from 'CoreHome';

interface DismissPromoWidgetDirectiveValue {
  widgetName: string;
  onClickHandler?: (event: Event) => void;
}

function onClickDismissPromoWidgetLink(
  binding: DirectiveBinding<DismissPromoWidgetDirectiveValue>,
  event: Event,
) {
  const { widgetName } = binding.value;
  const currentCategory = ReportingMenuStore.activeCategory.value as string;

  event.preventDefault();

  Matomo.helper.showAjaxLoading();

  return AjaxHelper.post({
    method: 'ProfessionalServices.dismissWidget',
  }, {
    widgetName,
  }).catch((e) => {
    Matomo.helper.hideAjaxLoading();
    throw e;
  }).then(() => {
    ReportingMenuStore.reloadMenuItems().then(() => {
      Matomo.helper.hideAjaxLoading();
      MatomoUrl.updateHash('category=Dashboard_Dashboard&subcategory=1');
      NotificationsStore.show({
        id: 'ProfessionalServices_PromoWidgetDismissed',
        animate: false,
        context: 'info',
        noclear: true,
        message: translate('ProfessionalServices_DismissedNotification', translate(currentCategory)),
        type: 'toast',
      });
    });
  });
}

export default {
  mounted(element: HTMLElement, binding: DirectiveBinding<DismissPromoWidgetDirectiveValue>): void {
    const { widgetName } = binding.value;
    if (!widgetName) {
      return;
    }

    binding.value.onClickHandler = onClickDismissPromoWidgetLink.bind(null, binding);
    element.addEventListener('click', binding.value.onClickHandler!);
  },
  unmounted(
    element: HTMLElement,
    binding: DirectiveBinding<DismissPromoWidgetDirectiveValue>,
  ): void {
    element.removeEventListener('click', binding.value.onClickHandler!);
  },
};
