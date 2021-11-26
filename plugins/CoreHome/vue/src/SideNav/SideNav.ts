/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import DirectiveUtilities from '../directiveUtilities';

interface SideNavArgs {
  activator: HTMLElement | string;
  initialized?: boolean;
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<SideNavArgs>): void {
    if (!binding.value.activator) {
      return;
    }

    setTimeout(() => {
      if (!binding.value.initialized) {
        binding.value.initialized = true;

        const sideNavActivator = DirectiveUtilities.getRef(binding.value.activator, binding);
        window.$(sideNavActivator).show();

        sideNavActivator.getAttribute('')
      }
    });
    // TODO
    /*
                    if (attr.piwikSideNav) {
                    $timeout(function () {
                        if (!initialized) {
                            initialized = true;

                            var sideNavActivator = $(attr.piwikSideNav).show();

                            $('#' + sideNavActivator.attr('data-target')).sidenav({
                                closeOnClick: true
                            });
                        }

                        if (element.hasClass('collapsible')) {
                            element.collapsible();
                        }
                    });
                }

     */
  },
};
