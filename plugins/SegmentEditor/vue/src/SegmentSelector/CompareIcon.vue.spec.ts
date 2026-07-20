/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const CompareIcon = require('./CompareIcon.vue').default;

describe('SegmentEditor/CompareIcon.vue', () => {
  it('renders transparent fills until the segment is actively compared', async () => {
    const wrapper = mount(CompareIcon, {
      props: {
        state: '',
      },
    });

    expect(wrapper.findAll('path').every((path) => path.attributes('fill') === 'transparent')).toBe(true);

    await wrapper.setProps({ state: 'active' });

    expect(wrapper.findAll('path').every((path) => path.attributes('fill') === 'currentColor')).toBe(true);
  });

  it('keeps the compare icon unfilled when comparison is disabled', () => {
    const wrapper = mount(CompareIcon, {
      props: {
        state: 'disabled',
      },
    });

    expect(wrapper.findAll('path').every((path) => path.attributes('fill') === 'transparent')).toBe(true);
  });
});
