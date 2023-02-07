/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface VisitorMapValue {
  config: any; // eslint-disable-line @typescript-eslint/no-explicit-any
  locale: any; // eslint-disable-line @typescript-eslint/no-explicit-any
  reqParams: any; // eslint-disable-line @typescript-eslint/no-explicit-any
  countryNames: any; // eslint-disable-line @typescript-eslint/no-explicit-any
}

const { $ } = window;

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<VisitorMapValue>): void {
    const config = { ...binding.value.config };
    config._ = binding.value.locale;
    config.reqParams = binding.value.reqParams;
    config.countryNames = binding.value.countryNames;

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const { VisitorMap } = (window as any).UserCountryMapLegacyModule;

    let visitorMap: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    if ($('#dashboardWidgetsArea').length) {
      // dashboard mode
      const $widgetContent = $('.UserCountryMap').parents('.widgetContent').first();

      $widgetContent.on('widget:create', (evt: unknown, widget: unknown) => {
        visitorMap = new VisitorMap(config, widget);
      }).on('widget:maximise', () => {
        visitorMap.resize();
      }).on('widget:minimise', () => {
        visitorMap.resize();
      }).on('widget:destroy', () => {
        visitorMap.destroy();
      });
    } else {
      // stand-alone mode
      visitorMap = new VisitorMap(config);
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (window as any).visitorMap = visitorMap;
  },
};
