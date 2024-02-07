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
  NotificationsStore,
  translate,
} from 'CoreHome';
import ClickEvent = JQuery.ClickEvent;

interface PluginStartFreeTrialDirectiveValue {
  // input
  pluginName: string;

  // state
  onClickHandler?: (event: ClickEvent) => void;
}

function onClickPluginNameLink(
  binding: DirectiveBinding<PluginStartFreeTrialDirectiveValue>,
  event: ClickEvent,
) {
  const { pluginName } = binding.value;

  event.preventDefault();
  window.Piwik_Popover.showLoading('');

  AjaxHelper.post(
    {
      module: 'API',
      method: 'Marketplace.startFreeTrial',
    },
    { pluginName },
  ).then(() => {
    window.Piwik_Popover.close();

    const notificationInstanceId = NotificationsStore.show({
      message: translate(
        'CorePluginsAdmin_PluginFreeTrialStarted',
        '<strong>',
        '</strong>',
        pluginName,
      ),
      context: 'success',
      id: 'startTrialSuccess',
      type: 'transient',
    });
    NotificationsStore.scrollToNotification(notificationInstanceId);
    Matomo.helper.redirect();
  }).catch((error) => {
    window.Piwik_Popover.showError('', error.message);
  });
}

const { $ } = window;

export default {
  mounted(
    element: HTMLElement,
    binding: DirectiveBinding<PluginStartFreeTrialDirectiveValue>,
  ): void {
    const { pluginName } = binding.value;

    if (!pluginName) {
      return;
    }

    binding.value.onClickHandler = onClickPluginNameLink.bind(null, binding);

    $(element)
      .on('click', binding.value.onClickHandler!)
      // attribute added for AnonymousPiwikUsageMeasurement
      .attr('matomo-plugin-name', pluginName);
  },
  unmounted(
    element: HTMLElement,
    binding: DirectiveBinding<PluginStartFreeTrialDirectiveValue>,
  ): void {
    $(element).off('click', binding.value.onClickHandler!);
  },
};
