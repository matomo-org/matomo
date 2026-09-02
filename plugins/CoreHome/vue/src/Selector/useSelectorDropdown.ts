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
  close: () => void;
}

const FOCUSABLE = '[role^="menuitem"], [role="option"]';

function visibleItems(root: HTMLElement | null, match: string): HTMLElement[] {
  if (!root?.querySelectorAll) {
    return [];
  }

  // A folded submenu is still in the DOM, so the arrows have to step over what it holds.
  return Array.from(root.querySelectorAll<HTMLElement>(match)).filter(
    (item) => !item.closest('.mtm-dropdownPanel__submenu:not(.mtm-dropdownPanel__submenu--open)'),
  );
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
  const walkable = options.role !== 'dialog';
  const match = options.items || FOCUSABLE;

  function focusAt(index: number, from = -1) {
    const items = visibleItems(panel(), match);
    if (!items.length) {
      return;
    }

    const at = from === -1 ? index : (from + index + items.length) % items.length;
    items[Math.max(0, Math.min(at, items.length - 1))].focus();
  }

  function step(by: number) {
    const items = visibleItems(panel(), match);
    if (!items.length) {
      return;
    }

    const at = items.indexOf(document.activeElement as HTMLElement);
    if (at === -1) {
      // Nothing inside is focused, so a step down enters at the top and a step up at the bottom.
      focusAt(by > 0 ? 0 : items.length - 1);
      return;
    }

    focusAt(by, at);
  }

  function close() {
    expanded.value = false;
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
        if (walkable && (event as MouseEvent).detail === 0) {
          setTimeout(() => focusAt(0), 0);
        }
      },
      onClosed: (event: MouseEvent|KeyboardEvent) => {
        expanded.value = false;
        // Escape and a keyboard-activated entry leave the focus in a panel about to disappear; a
        // pointer left it where the user put it, and taking it would draw a ring nobody asked for.
        const byKeyboard = event.type === 'keyup' || (event as MouseEvent).detail === 0;
        if (byKeyboard && panel()?.contains(document.activeElement)) {
          trigger()?.focus();
        }
      },
    }),

    onKeydown: (event: KeyboardEvent) => {
      if (!walkable) {
        return;
      }

      const keys: Record<string, () => void> = {
        ArrowDown: () => step(1),
        ArrowUp: () => step(-1),
        Home: () => focusAt(0),
        End: () => focusAt(visibleItems(panel(), match).length - 1),
      };

      if (keys[event.key]) {
        event.preventDefault();
        keys[event.key]();
      }
    },

    close,
  };
}
