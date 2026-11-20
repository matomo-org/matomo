/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// read at module scope or on mount, so these have to exist before the import below
vi.hoisted(() => {
  (window as unknown as { ListingFormatter: unknown }).ListingFormatter = {
    formatAnd: (values: string[]) => values.join(', '),
  };
  (window as unknown as { Mousetrap: unknown }).Mousetrap = { bind: () => {} };
  (window as unknown as { vueSanitize: unknown }).vueSanitize = (value: string) => value;
});

/* eslint-disable import/first */
import QuickAccess from './QuickAccess.vue';

// jsdom only populates event.which via the constructor, and trigger() throws assigning it
function pressKey(element: Element, which: number, isComposing = false): void {
  element.dispatchEvent(new KeyboardEvent('keydown', {
    which,
    isComposing,
    bubbles: true,
  } as KeyboardEventInit));
}

function mountQuickAccess() {
  return mount(QuickAccess, {
    global: {
      mocks: {
        translate: (key: string) => key,
      },
    },
    attachTo: document.body,
  });
}

// searchMenu is replaced by its debounced wrapper in created(), so stub it on the instance to
// observe the searches a change to the field triggers without waiting out the debounce
function stubSearchMenu(wrapper: ReturnType<typeof mountQuickAccess>) {
  const searchMenu = vi.fn();
  (wrapper.vm as unknown as { searchMenu: unknown }).searchMenu = searchMenu;
  return searchMenu;
}

describe('CoreHome/QuickAccess', () => {
  it('renders a single search input through the SearchInput component', () => {
    const wrapper = mountQuickAccess();

    expect(wrapper.findAll('.mtm-searchInput').length).toBe(1);
    expect(wrapper.findAll('input').length).toBe(1);
    expect(wrapper.find('.mtm-searchInput .icon-search').exists()).toBe(true);

    const input = wrapper.find('input');
    expect(input.classes()).toContain('mtm-searchInput__input');
    expect(input.attributes('placeholder')).toBe('General_Search');
    expect(input.attributes('tabindex')).toBe('5');
    expect(input.attributes('title')).toBe('CoreHome_QuickAccessTitle');

    wrapper.unmount();
  });

  it('keeps the search term in sync with the field in both directions', async () => {
    const wrapper = mountQuickAccess();
    const vm = wrapper.vm as unknown as { searchTerm: string };

    await wrapper.find('input').setValue('page');

    expect(vm.searchTerm).toBe('page');

    vm.searchTerm = 'visitors';
    await wrapper.vm.$nextTick();

    expect((wrapper.find('input').element as HTMLInputElement).value).toBe('visitors');

    wrapper.unmount();
  });

  it('activates the search when the field is focused, showing the results dropdown', async () => {
    const wrapper = mountQuickAccess();
    const vm = wrapper.vm as unknown as { searchActive: boolean };

    await wrapper.find('input').setValue('page');
    // the dropdown is shown only once the search is both non-empty and active
    expect(wrapper.find('.quickAccessDropdown').isVisible()).toBe(false);

    await wrapper.find('input').trigger('focus');

    expect(vm.searchActive).toBe(true);
    expect(wrapper.find('.quickAccessDropdown').isVisible()).toBe(true);

    wrapper.unmount();
  });

  it('deactivates the search when escape is pressed in the field', async () => {
    const wrapper = mountQuickAccess();
    const vm = wrapper.vm as unknown as { searchTerm: string; searchActive: boolean };

    await wrapper.find('input').setValue('page');
    await wrapper.find('input').trigger('focus');

    pressKey(wrapper.find('input').element, 27);
    await wrapper.vm.$nextTick();

    expect(vm.searchTerm).toBe('');
    expect(vm.searchActive).toBe(false);
    expect(document.activeElement).not.toBe(wrapper.find('input').element);

    wrapper.unmount();
  });

  it('searches when an IME candidate is committed without a further keystroke', async () => {
    const wrapper = mountQuickAccess();
    const searchMenu = stubSearchMenu(wrapper);

    const input = wrapper.find('input');
    const element = input.element as HTMLInputElement;

    await input.trigger('compositionstart');
    element.value = 'にほn';
    await input.trigger('input');

    expect(searchMenu).not.toHaveBeenCalled();

    // committing with the mouse fires compositionend, never a keydown
    element.value = '日本';
    await input.trigger('compositionend');

    expect(searchMenu).toHaveBeenCalledWith('日本');

    wrapper.unmount();
  });

  it('searches when text arrives without a keystroke, as when pasted', async () => {
    const wrapper = mountQuickAccess();
    const searchMenu = stubSearchMenu(wrapper);

    const input = wrapper.find('input');
    (input.element as HTMLInputElement).value = 'pages';
    await input.trigger('input');

    expect(searchMenu).toHaveBeenCalledWith('pages');

    wrapper.unmount();
  });

  it('does not act on keys belonging to the IME candidate window', async () => {
    const wrapper = mountQuickAccess();
    const clickItem = vi.fn();
    (wrapper.vm as unknown as { clickQuickAccessMenuItem: unknown })
      .clickQuickAccessMenuItem = clickItem;

    await wrapper.find('input').setValue('page');
    await wrapper.find('input').trigger('focus');

    pressKey(wrapper.find('input').element, 13, true);
    await wrapper.vm.$nextTick();

    expect(clickItem).not.toHaveBeenCalled();

    // once composition has ended, Enter selects again
    pressKey(wrapper.find('input').element, 13);
    await wrapper.vm.$nextTick();

    expect(clickItem).toHaveBeenCalled();

    wrapper.unmount();
  });

  it('does not search when the field is cleared', async () => {
    const wrapper = mountQuickAccess();

    await wrapper.find('input').setValue('page');
    const searchMenu = stubSearchMenu(wrapper);

    (wrapper.vm as unknown as { deactivateSearch: () => void }).deactivateSearch();
    await wrapper.vm.$nextTick();

    expect(searchMenu).not.toHaveBeenCalled();

    wrapper.unmount();
  });

  it('focuses the field when the search is activated from the keyboard shortcut', async () => {
    vi.useFakeTimers();

    try {
      const wrapper = mountQuickAccess();

      (wrapper.vm as unknown as { activateSearch: () => void }).activateSearch();
      await wrapper.vm.$nextTick();
      vi.advanceTimersByTime(10);

      expect(document.activeElement).toBe(wrapper.find('input').element);

      wrapper.unmount();
    } finally {
      vi.useRealTimers();
    }
  });
});
