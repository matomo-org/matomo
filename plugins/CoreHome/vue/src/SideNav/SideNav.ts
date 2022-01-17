/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/ban-ts-comment */

import { DirectiveBinding } from 'vue';
import DirectiveUtilities from '../directiveUtilities';

interface SideNavArgs {
  activator: HTMLElement | string;
}

let initialized = false;

/**
 * Will activate the materialize side nav feature once rendered. We use this directive as
 * it makes sure the actual left menu is rendered at the time we init the side nav.
 *
 * Has to be set on a collaapsible element
 *
 * Example:
 * <div class="collapsible" v-side-nav="nav .activateLeftMenu">...</div>
 */
export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<SideNavArgs>): void {
    if (!binding.value.activator) {
      return;
    }

    setTimeout(() => {
      if (!initialized) {
        initialized = true;

        const sideNavActivator = DirectiveUtilities.getRef(binding.value.activator, binding);
        if (sideNavActivator) {
          window.$(sideNavActivator).show();

          const targetSelector = sideNavActivator.getAttribute('data-target');

          // @ts-ignore
          window.$(`#${targetSelector}`).sidenav({
            closeOnClick: true,
          });
        }
      }

      if (el.classList.contains('collapsible')) {
        window.$(el).collapsible();
      }
    });
  },
};
