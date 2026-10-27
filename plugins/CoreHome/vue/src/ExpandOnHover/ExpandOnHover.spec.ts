/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';
import ExpandOnHover from './ExpandOnHover';

// ExpandOnHover binds the expander in a timeout
const nextTimeout = () => new Promise((resolve) => { setTimeout(resolve, 0); });

describe('CoreHome/ExpandOnHover', () => {
  async function mountExpandable(expandedClass?: string | string[]) {
    const wrapper = mount(defineComponent({
      directives: { ExpandOnHover },
      data: () => ({ expandedClass }),
      template: `
        <div v-expand-on-hover="{ expander: 'expander', expandedClass }">
          <span ref="expander">Hover me</span>
        </div>
      `,
    }), { attachTo: document.body });
    await nextTimeout();

    return wrapper;
  }

  it('carries the bare expanded class while hovered by default', async () => {
    const wrapper = await mountExpandable();

    await wrapper.find('span').trigger('mouseenter');
    expect(wrapper.classes()).toContain('expanded');

    await wrapper.trigger('mouseleave');
    expect(wrapper.classes()).not.toContain('expanded');

    wrapper.unmount();
  });

  it('carries the class it is given in place of the bare one', async () => {
    const wrapper = await mountExpandable('mtm-selector--expanded');

    await wrapper.find('span').trigger('mouseenter');
    expect(wrapper.classes()).toContain('mtm-selector--expanded');
    expect(wrapper.classes()).not.toContain('expanded');

    wrapper.unmount();
  });

  it('carries every class it is given while hovered, and drops them all on leave', async () => {
    const wrapper = await mountExpandable(['mtm-selector--expanded', 'expanded']);

    await wrapper.find('span').trigger('mouseenter');
    expect(wrapper.classes()).toEqual(expect.arrayContaining(['mtm-selector--expanded', 'expanded']));

    await wrapper.trigger('mouseleave');
    expect(wrapper.classes()).not.toContain('mtm-selector--expanded');
    expect(wrapper.classes()).not.toContain('expanded');

    wrapper.unmount();
  });
});
