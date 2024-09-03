/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import { MatomoUrl } from 'CoreHome';
import ClickEvent = JQuery.ClickEvent;

const { $ } = window;

window.broadcast.addPopoverHandler('browsePluginDetail', (value) => {
  let pluginName = value;
  let activeTab = null;

  if (value.indexOf('!') !== -1) {
    activeTab = value.slice(value.indexOf('!') + 1);
    pluginName = value.slice(0, value.indexOf('!'));
  }

  // use marketplace popover if marketplace is loaded
  if (
    MatomoUrl.urlParsed.value.module === 'Marketplace'
    && MatomoUrl.urlParsed.value.action === 'overview'
  ) {
    window.broadcast.propagateNewPopoverParameter('');
    MatomoUrl.updateHash({
      ...MatomoUrl.hashParsed.value,
      showPlugin: pluginName,
      popover: null,
    });

    return;
  }

  let url = `module=Marketplace&action=pluginDetails&pluginName=${encodeURIComponent(pluginName)}`;
  if (activeTab) {
    url += `&activeTab=${encodeURIComponent(activeTab)}`;
  }

  window.Piwik_Popover.createPopupAndLoadUrl(url, 'details');
});

interface PluginNameDirectiveValue {
  // input
  pluginName: string;
  activePluginTab?: string;

  // state
  onClickHandler?: (event: ClickEvent) => void;
}

function onClickPluginNameLink(
  binding: DirectiveBinding<PluginNameDirectiveValue>,
  event: ClickEvent,
) {
  let { pluginName } = binding.value;
  const { activePluginTab } = binding.value;

  event.preventDefault();

  if (activePluginTab) {
    pluginName += `!${activePluginTab}`;
  }

  window.broadcast.propagateNewPopoverParameter('browsePluginDetail', pluginName);
}

export default {
  mounted(element: HTMLElement, binding: DirectiveBinding<PluginNameDirectiveValue>): void {
    const { pluginName } = binding.value;
    if (!pluginName) {
      return;
    }

    binding.value.onClickHandler = onClickPluginNameLink.bind(null, binding);
    $(element).on('click', binding.value.onClickHandler!)
      // attribute added for AnonymousPiwikUsageMeasurement
      .attr('matomo-plugin-name', pluginName);
  },
  unmounted(element: HTMLElement, binding: DirectiveBinding<PluginNameDirectiveValue>): void {
    $(element).off('click', binding.value.onClickHandler!);
  },
};
