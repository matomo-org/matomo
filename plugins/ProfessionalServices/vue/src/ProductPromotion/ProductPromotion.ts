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
  confirmElement?: HTMLElement | null;
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

  // Already asked for. The button is disabled once the request succeeds, which stops a
  // real click, but not a programmatic one - and asking twice would send the super users
  // a second email for the same plugin.
  const requestTrial = element.querySelector<HTMLButtonElement>('[data-role=requestTrial]');

  if (requestTrial && requestTrial.disabled) {
    return;
  }

  // Held from when the directive mounted rather than looked up now. modalConfirm() moves
  // this node out of the banner and into a modal on the body, and never puts it back, so
  // a fresh query finds nothing the second time and the link would quietly do nothing
  // after the user declines once.
  const confirm = binding.value.confirmElement;
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

        // The banner stays put and its button becomes the same disabled "Trial requested"
        // state the Marketplace uses, so the click is confirmed where the user made it.
        // It does not need to survive a reload: a pending trial request makes the promotion
        // ineligible, so the next dashboard load leaves it out altogether.
        if (requestTrial) {
          requestTrial.disabled = true;
          requestTrial.classList.add('productPromotion__ctaButton--requested');
          requestTrial.textContent = translate('Marketplace_TrialRequested');
        }
      }).catch(() => {
        // The request failed, so no trial is pending and the banner has to stay: removing
        // it would hide the only way back to this offer. Without this the rejection is an
        // unhandled promise and the user is told nothing at all.
        NotificationsStore.show({
          message: translate('ProfessionalServices_PromotionTrialRequestFailed'),
          context: 'error',
          id: 'productPromotionTrialRequestFailed',
          placeat: '#notificationContainer',
          type: 'transient',
        });
      });
    },
  });
}

/**
 * Drops the artwork from the layout when it cannot be loaded, so that a missing image costs
 * the reader nothing: the copy, the call to action and the dismiss control stay where they
 * are and take the space back. Without this the figure keeps its column and the banner
 * carries an empty panel, or a broken-image icon, for the rest of the page's life.
 */
function hideFigureIfImageFails(element: HTMLElement): void {
  const image = element.querySelector<HTMLImageElement>('.productPromotion__image');

  if (!image) {
    return;
  }

  const hideFigure = () => element.classList.add('productPromotion--noFigure');

  // The failure may already have happened - a cached 404, or an image that finished while
  // the directive was still being mounted - in which case no event is coming.
  if (image.complete && image.naturalWidth === 0) {
    hideFigure();
    return;
  }

  image.addEventListener('error', hideFigure, { once: true });
}

export default {
  mounted(
    element: HTMLElement,
    binding: DirectiveBinding<ProductPromotionDirectiveValue>,
  ): void {
    if (!binding.value?.pluginName) {
      return;
    }

    hideFigureIfImageFails(element);

    const dismiss = element.querySelector<HTMLElement>('[data-role=dismiss]');
    if (dismiss) {
      binding.value.onDismissHandler = onDismiss.bind(null, element, binding);
      dismiss.addEventListener('click', binding.value.onDismissHandler!);
    }

    const requestTrial = element.querySelector<HTMLElement>('[data-role=requestTrial]');
    if (requestTrial) {
      binding.value.confirmElement = element
        .querySelector<HTMLElement>('[data-role=requestTrialConfirm]');
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
