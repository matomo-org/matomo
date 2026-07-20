/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const StarIcon = require('./StarIcon.vue').default;

describe('SegmentEditor/StarIcon.vue', () => {
  it('renders an unfilled star by default', () => {
    const wrapper = mount(StarIcon);

    expect(wrapper.find('path').attributes('fill')).toBe('none');
  });

  it('renders a filled star when the segment is starred', async () => {
    const wrapper = mount(StarIcon, {
      props: {
        filled: false,
      },
    });

    await wrapper.setProps({ filled: true });

    expect(wrapper.find('path').attributes('fill')).toBe('currentColor');
  });
});
