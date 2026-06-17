/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import DateRangePicker from './DateRangePicker.vue';

describe('DateRangePicker', () => {
  function mountPicker(customProps = {}) {
    return mount(DateRangePicker, {
      props: {
        startDate: '2026-03-11',
        endDate: '2026-06-08',
        disabled: false,
        ...customProps,
      },
      global: {
        mocks: {
          translate: (key: string) => key,
        },
        stubs: {
          DatePicker: true,
        },
      },
    });
  }

  it('does not emit rangeChange when syncing new prop values from the parent', async () => {
    const wrapper = mountPicker();

    expect(wrapper.emitted('rangeChange')).toBeUndefined();

    await wrapper.setProps({
      startDate: '2026-05-10',
      endDate: '2026-06-08',
    });

    expect(wrapper.emitted('rangeChange')).toBeUndefined();
  });

  it('emits rangeChange for direct user edits', async () => {
    const wrapper = mountPicker();

    (wrapper.vm as unknown as { setStartRangeDateFromStr: (value: string) => void })
      .setStartRangeDateFromStr('2026-05-10');

    expect(wrapper.emitted('rangeChange')?.[0]?.[0]).toEqual({
      start: '2026-05-10',
      end: '2026-06-08',
    });
  });
});
