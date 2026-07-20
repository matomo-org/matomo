/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/ban-ts-comment */

import { DirectiveBinding } from 'vue';
import DirectiveUtilities from '../directiveUtilities';

interface SideNavArgs {
  activator: HTMLElement | string;

  // directive state
  initialized?: boolean;
}

export function openMobileLeftMenu(): void {
  const mobileLeftMenu = document.getElementById('mobile-left-menu');
  if (!mobileLeftMenu) {
    return;
  }

  try {
    window.$(mobileLeftMenu).sidenav('open');
  } catch (e) {
    // Not initialized outside mobile layouts.
  }
}

export function closeMobileLeftMenu(): void {
  const secondNavBar = document.getElementById('secondNavBar');
  if (!secondNavBar?.classList.contains('mobileLeftMenuOpen')) {
    return;
  }

  const mobileLeftMenu = document.getElementById('mobile-left-menu');
  if (!mobileLeftMenu) {
    return;
  }

  try {
    window.$(mobileLeftMenu).sidenav('close');
  } catch (e) {
    // The mobile sidenav is not initialized outside mobile layouts.
  }
}

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
    const secondNavBar = document.getElementById('secondNavBar');
    const setSecondNavBarMenuState = (isOpen: boolean) => {
      if (secondNavBar) {
        secondNavBar.classList.toggle('mobileLeftMenuOpen', isOpen);
      }
    };
    setTimeout(() => {
      if (!binding.value.initialized) {
        binding.value.initialized = true;

        const sideNavActivator = DirectiveUtilities.getRef(binding.value.activator, binding);
        if (sideNavActivator) {
          window.$(sideNavActivator).show();

          const targetSelector = sideNavActivator.getAttribute('data-target');
          // @ts-ignore
          window.$(`#${targetSelector}`).sidenav({
            closeOnClick: true,
            onOpenStart: () => {
              setSecondNavBarMenuState(true);
            },
            onCloseStart: () => {
              setSecondNavBarMenuState(false);
            },
          });
        }
      }

      if (el.classList.contains('collapsible')) {
        window.$(el).collapsible();
      }
    });
  },
};
