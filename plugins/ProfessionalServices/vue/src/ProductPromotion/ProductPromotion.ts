/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import {
  AjaxHelper,
  Matomo,
  NotificationsStore,
  translate,
} from 'CoreHome';

interface ProductPromotionDirectiveValue {
  pluginName: string;
  triggerName: string;
  productName: string;
  onDismissHandler?: (event: Event) => void;
  onRequestTrialHandler?: (event: Event) => void;
}

function onDismiss(
  element: HTMLElement,
  binding: DirectiveBinding<ProductPromotionDirectiveValue>,
  event: Event,
) {
  event.preventDefault();

  Matomo.helper.showAjaxLoading();

  return AjaxHelper.post({
    method: 'ProfessionalServices.dismissDashboardPromotion',
  }, {
    pluginName: binding.value.pluginName,
    triggerName: binding.value.triggerName,
  }).catch((e) => {
    Matomo.helper.hideAjaxLoading();
    throw e;
  }).then(() => {
    Matomo.helper.hideAjaxLoading();
    element.remove();
  });
}

function onRequestTrial(
  element: HTMLElement,
  binding: DirectiveBinding<ProductPromotionDirectiveValue>,
  event: Event,
) {
  event.preventDefault();

  const confirm = element.querySelector<HTMLElement>('[data-role=requestTrialConfirm]');
  if (!confirm) {
    return;
  }

  Matomo.helper.modalConfirm(confirm, {
    yes: () => {
      AjaxHelper.post({
        module: 'API',
        method: 'Marketplace.requestTrial',
      }, {
        pluginName: binding.value.pluginName,
      }).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate('Marketplace_RequestTrialSubmitted', binding.value.productName),
          context: 'success',
          id: 'productPromotionTrialRequested',
          placeat: '#notificationContainer',
          type: 'transient',
        });

        NotificationsStore.scrollToNotification(notificationInstanceId);

        // A pending trial request suppresses the promotion, so it would not come back on
        // the next dashboard load either.
        element.remove();
      });
    },
  });
}

export default {
  mounted(
    element: HTMLElement,
    binding: DirectiveBinding<ProductPromotionDirectiveValue>,
  ): void {
    if (!binding.value?.pluginName) {
      return;
    }

    const dismiss = element.querySelector<HTMLElement>('[data-role=dismiss]');
    if (dismiss) {
      binding.value.onDismissHandler = onDismiss.bind(null, element, binding);
      dismiss.addEventListener('click', binding.value.onDismissHandler!);
    }

    const requestTrial = element.querySelector<HTMLElement>('[data-role=requestTrial]');
    if (requestTrial) {
      binding.value.onRequestTrialHandler = onRequestTrial.bind(null, element, binding);
      requestTrial.addEventListener('click', binding.value.onRequestTrialHandler!);
    }
  },
  unmounted(
    element: HTMLElement,
    binding: DirectiveBinding<ProductPromotionDirectiveValue>,
  ): void {
    if (binding.value?.onDismissHandler) {
      element.querySelector<HTMLElement>('[data-role=dismiss]')
        ?.removeEventListener('click', binding.value.onDismissHandler);
    }

    if (binding.value?.onRequestTrialHandler) {
      element.querySelector<HTMLElement>('[data-role=requestTrial]')
        ?.removeEventListener('click', binding.value.onRequestTrialHandler);
    }
  },
};
