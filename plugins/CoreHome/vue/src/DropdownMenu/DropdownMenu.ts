/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding, nextTick } from 'vue';

interface DropdownArgs {
  activates?: HTMLElement|string,
}

/**
 * A materializecss dropdown menu that supports submenus.
 *
 * To use a submenu, just use this directive within another dropdown.
 *
 * Note: if submenus are used, then dropdowns will never scroll.
 *
 * Usage:
 * <a class='dropdown-trigger btn' href='' data-target='mymenu' v-dropdown-menu>Menu</a>
 * <ul id='mymenu' class='dropdown-content'>
 *     <li>
 *         <a class='dropdown-trigger' data-target="mysubmenu" v-dropdown-menu>Submenu</a>
 *         <ul id="mysubmenu" class="dropdown-content">
 *             <li>Submenu Item</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <a href="">Another item</a>
 *     </li>
 * </ul>
 */
export default {
  mounted(element: HTMLElement, binding: DirectiveBinding<DropdownArgs>): void {
    let options = {};

    $(element).addClass('matomo-dropdown-menu');

    const isSubmenu = !!$(element).parent().closest('.dropdown-content').length;
    if (isSubmenu) {
      options = { hover: true };
      $(element).addClass('submenu');
      $(binding.value?.activates || $(element).data('target'))
        .addClass('submenu-dropdown-content');

      // if a submenu is used, the dropdown will never scroll
      $(element).parents('.dropdown-content').addClass('submenu-container');
    }

    $(element).dropdown(options);
  },
  updated(element: HTMLElement): void {
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    nextTick(() => {
      $(element).addClass('matomo-dropdown-menu');
    });
  },
};
