/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// The keyboard and ARIA behaviour under test lives in ExpandOnClick and useSelectorDropdown, so
// those come from their sources; only translate is replaced.
vi.mock('CoreHome', async () => ({
  translate: (key: string) => key,
  activateMenuItem: (await import('../../../../CoreHome/vue/src/DataTable/activateMenuItem')).default,
  ExpandOnClick: (await import('../../../../CoreHome/vue/src/ExpandOnClick/ExpandOnClick')).default,
  useSelectorDropdown: (await import('../../../../CoreHome/vue/src/Selector/useSelectorDropdown')).default,
}));

import MetricsPicker from './MetricsPicker.vue';

const selectableColumns = [
  { column: 'nb_visits', translation: 'Visits' },
  { column: 'nb_actions', translation: 'Actions' },
];

const selectableRows = [
  { matcher: 'Row 1', label: 'Row 1' },
  { matcher: 'Row 2', label: 'Row 2' },
];

// ExpandOnClick binds the expander, and the panel focuses its first entry, in a timeout
const nextTimeout = () => new Promise((resolve) => { setTimeout(resolve, 0); });

// trigger('click') reports no pointer (`detail: 0`), which is what a keyboard produces; Vue Test
// Utils cannot set `detail`, so a real pointer click is dispatched by hand.
function mouseClick(element: Element) {
  element.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, detail: 1 }));
}

describe('CoreVisualizations/MetricsPicker.vue', () => {
  let wrapper: ReturnType<typeof mount>;

  async function mountPicker(multiselect = true) {
    wrapper = mount(MetricsPicker, {
      props: {
        multiselect,
        selectableColumns,
        selectableRows,
        selectedColumns: ['nb_visits'],
      },
      global: { mocks: { translate: (key: string) => key } },
      attachTo: document.body,
    });
    await nextTimeout();
  }

  const trigger = () => wrapper.find('.mtm-selector__trigger');
  const panel = () => wrapper.find('.mtm-selector__dropdown');
  const items = () => panel().findAll('[role^="menuitem"]').map((item) => item.element as HTMLElement);

  async function openByKeyboard() {
    await trigger().trigger('click');
    await nextTimeout();
  }

  async function openByMouse() {
    (trigger().element as HTMLElement).focus();
    mouseClick(trigger().element);
    await nextTimeout();
  }

  afterEach(() => {
    wrapper.unmount();
  });

  it('announces its menu and whether it is open', async () => {
    await mountPicker();
    expect(trigger().attributes('aria-haspopup')).toBe('menu');
    expect(trigger().attributes('aria-expanded')).toBe('false');

    await openByMouse();
    expect(trigger().attributes('aria-expanded')).toBe('true');

    mouseClick(trigger().element);
    await wrapper.vm.$nextTick();
    expect(trigger().attributes('aria-expanded')).toBe('false');
  });

  it('moves the focus to the first option when opened with the keyboard', async () => {
    await mountPicker();
    await openByKeyboard();

    expect(document.activeElement).toBe(items()[0]);
  });

  it('leaves the focus on the trigger when opened with the mouse', async () => {
    await mountPicker();
    await openByMouse();

    expect(document.activeElement).toBe(trigger().element);
  });

  it('walks metrics then records to plot, skipping the heading between them', async () => {
    await mountPicker();
    await openByKeyboard();

    const walkable = items();
    expect(walkable.map((item) => item.textContent?.trim())).toEqual(['Visits', 'Actions', 'Row 1', 'Row 2']);

    for (let i = 1; i < walkable.length; i += 1) {
      await panel().trigger('keydown', { key: 'ArrowDown' });
      expect(document.activeElement).toBe(walkable[i]);
    }
  });

  it('moves back to the previous option with ArrowUp', async () => {
    await mountPicker();
    await openByKeyboard();
    const walkable = items();

    await panel().trigger('keydown', { key: 'ArrowDown' });
    await panel().trigger('keydown', { key: 'ArrowDown' });
    expect(document.activeElement).toBe(walkable[2]);

    await panel().trigger('keydown', { key: 'ArrowUp' });
    expect(document.activeElement).toBe(walkable[1]);
  });

  it('wraps from the last option to the first, and back', async () => {
    await mountPicker();
    await openByKeyboard();
    const walkable = items();

    await panel().trigger('keydown', { key: 'ArrowUp' });
    expect(document.activeElement).toBe(walkable[walkable.length - 1]);

    await panel().trigger('keydown', { key: 'ArrowDown' });
    expect(document.activeElement).toBe(walkable[0]);
  });

  it('jumps to the first and last options with Home and End', async () => {
    await mountPicker();
    await openByKeyboard();
    const walkable = items();

    await panel().trigger('keydown', { key: 'End' });
    expect(document.activeElement).toBe(walkable[walkable.length - 1]);

    await panel().trigger('keydown', { key: 'Home' });
    expect(document.activeElement).toBe(walkable[0]);
  });

  it('keeps the walking keys from scrolling the page', async () => {
    await mountPicker();
    await openByKeyboard();

    ['ArrowDown', 'ArrowUp', 'Home', 'End'].forEach((key) => {
      const event = new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true });
      document.activeElement!.dispatchEvent(event);
      expect(event.defaultPrevented).toBe(true);
    });
  });

  [true, false].forEach((multiselect) => {
    it(`gives the focus back to the trigger once an option is picked with the keyboard (multiselect: ${multiselect})`, async () => {
      await mountPicker(multiselect);
      await openByKeyboard();

      const option = wrapper.findAll('.metricsPickerColumn')[1];
      (option.element as HTMLElement).focus();
      await option.trigger('keydown', { key: 'Enter' });

      expect(document.activeElement).toBe(trigger().element);
      expect(trigger().attributes('aria-expanded')).toBe('false');
      expect((wrapper.emitted('select')![0][0] as { byKeyboard: boolean }).byKeyboard).toBe(true);
    });
  });

  it('picks an option with Space as it does with Enter', async () => {
    await mountPicker();
    await openByKeyboard();

    const option = wrapper.findAll('.metricsPickerRow')[0];
    (option.element as HTMLElement).focus();
    await option.trigger('keydown', { key: ' ' });

    expect(document.activeElement).toBe(trigger().element);
    expect(wrapper.emitted('select')![0][0]).toEqual({
      columns: ['nb_visits'],
      rows: ['Row 1'],
      byKeyboard: true,
    });
  });

  it('closes on a mouse pick and leaves the focus where the click put it', async () => {
    await mountPicker();
    await openByMouse();

    const option = wrapper.findAll('.metricsPickerColumn')[1];
    (option.element as HTMLElement).focus();
    mouseClick(option.element);
    await wrapper.vm.$nextTick();

    expect(document.activeElement).toBe(option.element);
    expect(trigger().attributes('aria-expanded')).toBe('false');
    expect((wrapper.emitted('select')![0][0] as { byKeyboard: boolean }).byKeyboard).toBe(false);
  });
});
