/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import SearchInput from './SearchInput.vue';

describe('CoreHome/SearchInput', () => {
  it('renders the search icon and translated placeholder', () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: '',
      },
    });

    expect(wrapper.find('.icon-search').exists()).toBe(true);
    expect(wrapper.find('input').attributes('placeholder')).toBe('General_Search');
  });

  it('reflects the provided model value', () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: 'segment',
      },
    });

    expect((wrapper.find('input').element as HTMLInputElement).value).toBe('segment');
  });

  it('emits update:modelValue when the input value changes', async () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: '',
      },
    });

    await wrapper.find('input').setValue('country');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['country']);
  });

  it('clears to an empty string when the clear button is clicked', async () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: 'country',
        showClear: true,
      },
    });

    await wrapper.find('.mtm-searchInput__clear').trigger('click');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['']);
  });

  it('gives the clear button a translated title', () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: 'country',
        showClear: true,
      },
    });

    expect(wrapper.find('.mtm-searchInput__clear').attributes('title')).toBe('General_Clear');
  });

  it('applies attributes set on the component to the input, not to the wrapper', () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: '',
      },
      attrs: {
        tabindex: '5',
        title: 'Search menu entries',
      },
    });

    const input = wrapper.find('input');
    expect(input.attributes('tabindex')).toBe('5');
    expect(input.attributes('title')).toBe('Search menu entries');

    const wrapperDiv = wrapper.find('.mtm-searchInput');
    expect(wrapperDiv.attributes('tabindex')).toBeUndefined();
    expect(wrapperDiv.attributes('title')).toBeUndefined();
  });

  it('forwards a listener set on the component to the input exactly once', async () => {
    // guards inheritAttrs: false: otherwise the bubbling event would invoke the listener twice
    const onKeydown = vi.fn();
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: '',
      },
      attrs: {
        onKeydown,
        onFocus: onKeydown,
      },
    });

    await wrapper.find('input').trigger('keydown');

    expect(onKeydown).toHaveBeenCalledTimes(1);
  });

  it('focuses the input when the focused prop becomes true', async () => {
    vi.useFakeTimers();

    try {
      const wrapper = mount(SearchInput, {
        props: {
          modelValue: '',
          focused: false,
        },
        attachTo: document.body,
      });

      await wrapper.setProps({ focused: true });
      vi.advanceTimersByTime(10);

      expect(document.activeElement).toBe(wrapper.find('input').element);
      wrapper.unmount();
    } finally {
      vi.useRealTimers();
    }
  });

  it('blur() removes focus from the input', () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: '',
      },
      attachTo: document.body,
    });

    const input = wrapper.find('input').element as HTMLInputElement;
    input.focus();
    expect(document.activeElement).toBe(input);

    (wrapper.vm as unknown as { blur: () => void }).blur();

    expect(document.activeElement).not.toBe(input);
    wrapper.unmount();
  });

  it('does not emit while text is still being composed', async () => {
    const wrapper = mount(SearchInput, {
      props: {
        modelValue: '',
      },
    });

    const input = wrapper.find('input');
    const element = input.element as HTMLInputElement;

    await input.trigger('compositionstart');
    element.value = 'にほn';
    await input.trigger('input');

    expect(wrapper.emitted('update:modelValue')).toBeUndefined();

    element.value = '日本';
    await input.trigger('compositionend');

    expect(wrapper.emitted('update:modelValue')).toEqual([['日本']]);
  });
});
