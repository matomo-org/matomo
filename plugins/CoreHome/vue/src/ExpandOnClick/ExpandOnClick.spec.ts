/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';
import ExpandOnClick from './ExpandOnClick';

// ExpandOnClick binds the expander in a timeout
const nextTimeout = () => new Promise((resolve) => { setTimeout(resolve, 0); });

describe('CoreHome/ExpandOnClick', () => {
  async function mountExpandable(expandedClass?: string | string[]) {
    const wrapper = mount(defineComponent({
      directives: { ExpandOnClick },
      data: () => ({ expandedClass }),
      template: `
        <div v-expand-on-click="{ expander: 'expander', expandedClass }">
          <button ref="expander" type="button">Open</button>
        </div>
      `,
    }), { attachTo: document.body });
    await nextTimeout();

    return wrapper;
  }

  it('carries the bare expanded class while open by default', async () => {
    const wrapper = await mountExpandable();

    await wrapper.find('button').trigger('click');
    expect(wrapper.classes()).toContain('expanded');

    await wrapper.find('button').trigger('click');
    expect(wrapper.classes()).not.toContain('expanded');

    wrapper.unmount();
  });

  it('falls back on the bare expanded class for an empty list', async () => {
    const wrapper = await mountExpandable([]);

    await wrapper.find('button').trigger('click');
    expect(wrapper.classes()).toContain('expanded');

    await wrapper.find('button').trigger('click');
    expect(wrapper.classes()).not.toContain('expanded');

    wrapper.unmount();
  });

  it('carries every class it is given while open, and drops them all on Escape', async () => {
    const wrapper = await mountExpandable(['mtm-selector--expanded', 'expanded']);

    await wrapper.find('button').trigger('click');
    expect(wrapper.classes()).toEqual(expect.arrayContaining(['mtm-selector--expanded', 'expanded']));

    document.documentElement.dispatchEvent(new KeyboardEvent('keyup', { key: 'Escape' }));
    expect(wrapper.classes()).not.toContain('mtm-selector--expanded');
    expect(wrapper.classes()).not.toContain('expanded');

    wrapper.unmount();
  });

  // The first class holds the state: a plugin dropping its `expanded` hook must not leave the
  // directive thinking the element is closed.
  it('reads the open state from the first class only', async () => {
    const wrapper = await mountExpandable(['mtm-selector--expanded', 'expanded']);

    await wrapper.find('button').trigger('click');
    wrapper.element.classList.remove('expanded');

    await wrapper.find('button').trigger('click');
    expect(wrapper.classes()).not.toContain('mtm-selector--expanded');

    wrapper.unmount();
  });
});
