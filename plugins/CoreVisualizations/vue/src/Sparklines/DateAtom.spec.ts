/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import DateAtom from './DateAtom.vue';

describe('CoreVisualizations/DateAtom', () => {
  it('renders the label text', () => {
    const wrapper = mount(DateAtom as any, { props: { label: 'Monday, May 4, 2026' } });

    expect(wrapper.find('.dateAtom').text()).toBe('Monday, May 4, 2026');
  });

  it('exposes the full label as a title so a clipped date stays recoverable on hover', () => {
    const wrapper = mount(DateAtom as any, { props: { label: 'Sunday, May 3, 2026' } });

    expect(wrapper.find('.dateAtom').attributes('title')).toBe('Sunday, May 3, 2026');
  });
});
