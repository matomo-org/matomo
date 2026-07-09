/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// translate just echoes the key.
jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const MetricsPickerOptions = require('./MetricsPickerOptions.vue').default;

const selectableColumns = [
  { column: 'nb_visits', translation: 'Visits' },
  { column: 'nb_uniq_visitors', translation: 'Unique visitors' },
];

const selectableRows = [
  { matcher: 'Row 1', label: 'Row 1' },
  { matcher: 'Row 2', label: 'Row 2' },
];

function mountOptions(props: Record<string, unknown> = {}) {
  return mount(MetricsPickerOptions, {
    props: { selectableColumns, selectableRows, ...props },
    global: { mocks: { translate: (key: string) => key } },
  });
}

function lastSelect(wrapper: ReturnType<typeof mountOptions>) {
  const events = wrapper.emitted('select') as unknown[][];
  return events[events.length - 1][0];
}

describe('CoreVisualizations/MetricsPickerOptions.vue', () => {
  it('in single-select mode, picking an option clears any other selection and emits just that one', async () => {
    const wrapper = mountOptions({ multiselect: false, selectedColumns: ['nb_visits'] });

    await wrapper.findAll('.metrics-picker__row')[0].find('input').trigger('change');

    expect(lastSelect(wrapper)).toEqual({ columns: [], rows: ['Row 1'] });
  });

  it('in multiselect mode, selections accumulate across columns and rows', async () => {
    const wrapper = mountOptions({ multiselect: true, selectedColumns: ['nb_visits'] });

    await wrapper.findAll('.metrics-picker__column')[1].find('input').trigger('change');
    await wrapper.findAll('.metrics-picker__row')[0].find('input').trigger('change');

    expect(lastSelect(wrapper)).toEqual({
      columns: ['nb_visits', 'nb_uniq_visitors'],
      rows: ['Row 1'],
    });
  });

  it('in multiselect mode, clicking a selected option toggles it off', async () => {
    const wrapper = mountOptions({ multiselect: true, selectedColumns: ['nb_visits'] });

    await wrapper.findAll('.metrics-picker__column')[0].find('input').trigger('change');

    expect(lastSelect(wrapper)).toEqual({ columns: [], rows: [] });
  });
});
