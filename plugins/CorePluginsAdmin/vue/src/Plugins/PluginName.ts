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

function isOnMarketplaceOverview(): boolean {
  return MatomoUrl.urlParsed.value.module === 'Marketplace'
    && MatomoUrl.urlParsed.value.action === 'overview';
}

/**
 * The Marketplace page showing the plugin's details, keeping the site and period the reader is on.
 */
function getPluginDetailsUrl(pluginName: string): string {
  const { idSite, period, date } = MatomoUrl.urlParsed.value;
  const query = MatomoUrl.stringify({
    module: 'Marketplace',
    action: 'overview',
    idSite,
    period,
    date,
  });

  return `?${query}#?${MatomoUrl.stringify({ showPlugin: pluginName })}`;
}

function showPluginDetails(pluginName: string) {
  if (isOnMarketplaceOverview()) {
    MatomoUrl.updateHash({
      ...MatomoUrl.hashParsed.value,
      showPlugin: pluginName,
      popover: null,
    });

    return;
  }

  window.location.href = getPluginDetailsUrl(pluginName);
}

// URLs from before the details page opened the plugin in a popover (`popover=browsePluginDetail$3A
// Name!tab`). They are sent on to the page, which has no tabs, so the tab is dropped. The popover
// entry is replaced rather than left behind, or going back to it would open the page again.
window.broadcast.addPopoverHandler('browsePluginDetail', (value) => {
  const pluginName = value.indexOf('!') !== -1 ? value.slice(0, value.indexOf('!')) : value;

  if (isOnMarketplaceOverview()) {
    window.broadcast.propagateNewPopoverParameter('');
    showPluginDetails(pluginName);
    return;
  }

  window.location.replace(getPluginDetailsUrl(pluginName));
});

interface PluginNameDirectiveValue {
  // input
  pluginName: string;
  /** @deprecated the details page has no tabs, so this is ignored */
  activePluginTab?: string;

  // state
  onClickHandler?: (event: ClickEvent) => void;
}

function onClickPluginNameLink(
  binding: DirectiveBinding<PluginNameDirectiveValue>,
  event: ClickEvent,
) {
  // a new tab or window follows the link's own href
  if (event.ctrlKey || event.metaKey || event.shiftKey || event.button === 1) {
    return;
  }

  event.preventDefault();
  showPluginDetails(binding.value.pluginName);
}

export default {
  mounted(element: HTMLElement, binding: DirectiveBinding<PluginNameDirectiveValue>): void {
    const { pluginName } = binding.value;
    if (!pluginName) {
      return;
    }

    binding.value.onClickHandler = onClickPluginNameLink.bind(null, binding);
    $(element).on('click', binding.value.onClickHandler!)
      .attr('href', getPluginDetailsUrl(pluginName))
      // attribute added for AnonymousPiwikUsageMeasurement
      .attr('matomo-plugin-name', pluginName);
  },
  unmounted(element: HTMLElement, binding: DirectiveBinding<PluginNameDirectiveValue>): void {
    $(element).off('click', binding.value.onClickHandler!);
  },
};
