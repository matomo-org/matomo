/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ref, Ref } from 'vue';

/**
 * What every selector in the design system does, and nothing it draws.
 *
 * A trigger that folds a panel out, the state that says so, the keys that walk it and the focus
 * that follows. What varies between them - anchored left or right, one column or several, a menu
 * or a search field or a paragraph - is markup and stylesheet, and stays with the consumer. A
 * component that owned those too would need an option per selector; this one needs none.
 */

export interface SelectorDropdownOptions {
  // What the panel is, for anyone who cannot see it. `dialog` skips the arrow keys, having no list
  // to walk.
  role?: 'menu' | 'listbox' | 'dialog';
  // The class ExpandOnClick carries while open. Give the block its own modifier so its stylesheet
  // can nest it rather than reading a bare state class it does not own.
  expandedClass: string;
  // The selector matching what the arrows walk. Defaults to the roles a menu holds.
  items?: string;
  // Items inside a panel matching this are skipped: a folded submenu is still in the DOM.
  folded?: string;
}

export interface SelectorDropdown {
  expanded: Ref<boolean>;
  // Spread onto the trigger. A function rather than a computed ref: a consumer holding this in
  // Options API `data()` gets its refs unwrapped by Vue, and `.value` would read undefined there.
  triggerProps: () => Record<string, string>;
  // Spread into `v-expand-on-click`, alongside the `expander` ref name.
  expandBinding: (expander: string) => Record<string, unknown>;
  // Bind to the panel's root: @keydown="selector.onKeydown".
  onKeydown: (event: KeyboardEvent) => void;
  // Closing by hand, when a panel folds without the directive hearing it - picking an entry, say.
  // Takes the event so the focus goes back where a keyboard left it.
  closedBy: (event: MouseEvent|KeyboardEvent) => void;
  close: () => void;
}

const FOCUSABLE = '[role^="menuitem"], [role="option"]';
const FOLDED = '.mtm-dropdownPanel__submenu:not(.mtm-dropdownPanel__submenu--open)';

function visibleItems(root: HTMLElement | null, match: string, folded: string): HTMLElement[] {
  if (!root?.querySelectorAll) {
    return [];
  }

  return Array.from(root.querySelectorAll<HTMLElement>(match))
    .filter((item) => !item.closest(folded));
}

/**
 * `panel` is read when a key arrives, not when this is called, so a consumer may hand over a ref
 * that only fills in once the panel renders.
 */
export default function useSelectorDropdown(
  options: SelectorDropdownOptions,
  panel: () => HTMLElement | null,
  trigger: () => HTMLElement | null,
): SelectorDropdown {
  const expanded = ref(false);
  const walksItems = options.role !== 'dialog';
  const match = options.items || FOCUSABLE;
  const folded = options.folded || FOLDED;

  function items(): HTMLElement[] {
    return visibleItems(panel(), match, folded);
  }

  function focusIndex(index: number) {
    items()[index]?.focus();
  }

  function step(by: number) {
    const walkable = items();
    if (!walkable.length) {
      return;
    }

    const at = walkable.indexOf(document.activeElement as HTMLElement);
    if (at === -1) {
      // Nothing inside is focused, so a step down enters at the top and a step up at the bottom.
      focusIndex(by > 0 ? 0 : walkable.length - 1);
      return;
    }

    focusIndex((at + by + walkable.length) % walkable.length);
  }

  function close() {
    expanded.value = false;
  }

  // Escape and a keyboard-activated entry leave the focus in a panel about to disappear; a pointer
  // left it where the user put it, and taking it would draw a ring nobody asked for.
  function closedBy(event: MouseEvent|KeyboardEvent) {
    close();

    const byKeyboard = event.type === 'keyup' || (event as MouseEvent).detail === 0;
    if (byKeyboard && panel()?.contains(document.activeElement)) {
      trigger()?.focus();
    }
  }

  return {
    expanded,

    triggerProps: () => ({
      'aria-haspopup': options.role || 'menu',
      'aria-expanded': expanded.value ? 'true' : 'false',
    }),

    expandBinding: (expander: string) => ({
      expander,
      expandedClass: options.expandedClass,
      onExpand: (event: MouseEvent|KeyboardEvent) => {
        expanded.value = true;
        // A button opened with the keyboard reports no pointer, and only then does the panel take
        // the focus off the trigger.
        if (walksItems && (event as MouseEvent).detail === 0) {
          setTimeout(() => focusIndex(0), 0);
        }
      },
      onClosed: closedBy,
    }),

    onKeydown: (event: KeyboardEvent) => {
      if (!walksItems) {
        return;
      }

      const keys: Record<string, () => void> = {
        ArrowDown: () => step(1),
        ArrowUp: () => step(-1),
        Home: () => focusIndex(0),
        End: () => focusIndex(items().length - 1),
      };

      if (keys[event.key]) {
        event.preventDefault();
        keys[event.key]();
      }
    },

    closedBy,
    close,
  };
}
