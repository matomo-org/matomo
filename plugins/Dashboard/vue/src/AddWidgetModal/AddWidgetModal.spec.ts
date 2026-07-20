/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ref } from 'vue';
import { shallowMount } from '@vue/test-utils';

jest.useFakeTimers();

const mockMatomo = { on: jest.fn(), off: jest.fn() };

const mockWidgets = ref<Record<string, unknown[]>>({});

jest.mock('CoreHome', () => ({
  Matomo: mockMatomo,
  translate: (key: string) => key,
  WidgetsStore: {
    widgets: mockWidgets,
  },
  WidgetType: {},
  MatomoModal: { template: '<div><slot /></div>' },
  Widget: { template: '<div />' },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const AddWidgetModal = require('./AddWidgetModal.vue').default;

describe('Dashboard/AddWidgetModal', () => {
  function mountComponent() {
    return shallowMount(AddWidgetModal as any);
  }

  function getHandler(eventName: string) {
    return mockMatomo.on.mock.calls.find((call) => call[0] === eventName)?.[1];
  }

  beforeEach(() => {
    jest.clearAllMocks();
    mockWidgets.value = {};
  });

  it('opens when the Matomo open event fires', () => {
    const wrapper = mountComponent();

    getHandler('Dashboard.AddWidget.open')!();
    expect((wrapper.vm as any).isOpen).toBe(true);
  });

  it('snapshots the dashboard widget IDs on open', () => {
    const dashboardArea = document.createElement('div');
    dashboardArea.id = 'dashboardWidgetsArea';
    ['widget.one', 'widget.two'].forEach((id) => {
      const el = document.createElement('div');
      el.setAttribute('widgetId', id);
      dashboardArea.appendChild(el);
    });
    document.body.appendChild(dashboardArea);

    try {
      const wrapper = mountComponent();
      getHandler('Dashboard.AddWidget.open')!();

      const ids = (wrapper.vm as any).existingWidgetIds as Set<string>;
      expect(Array.from(ids).sort()).toEqual(['widget.one', 'widget.two']);
    } finally {
      document.body.removeChild(dashboardArea);
    }
  });

  it('exposes category names directly from the WidgetsStore', () => {
    mockWidgets.value = {
      Visitors: [{ uniqueId: 'Widget.unique' }],
      Goals: [],
    };
    const wrapper = mountComponent();

    expect((wrapper.vm as any).categoryNames).toEqual(['Visitors', 'Goals']);
  });

  it('returns the widgets of the chosen category', () => {
    const widget = { uniqueId: 'Widget.unique', name: 'My Widget' };
    mockWidgets.value = { Visitors: [widget] };
    const wrapper = mountComponent();

    (wrapper.vm as any).onCategoryChosen('Visitors');

    expect((wrapper.vm as any).widgetsInCategory).toEqual([widget]);
    expect((wrapper.vm as any).hoveredWidgetId).toBeNull();
  });

  it('updates the preview when a widget hover is emitted', () => {
    const widget = { uniqueId: 'Widget.unique', name: 'My Widget' };
    mockWidgets.value = { Visitors: [widget] };
    const wrapper = mountComponent();

    (wrapper.vm as any).onWidgetHover('Widget.unique');

    expect((wrapper.vm as any).previewWidget).toEqual(widget);
  });

  it('emits select and keeps the modal open when a widget is chosen', () => {
    const widget = { uniqueId: 'Widget.unique', parameters: { foo: 'bar' } };
    mockWidgets.value = { Visitors: [widget] };
    const wrapper = mountComponent();
    (wrapper.vm as any).isOpen = true;

    (wrapper.vm as any).onSelect('Widget.unique');

    expect(wrapper.emitted().select).toEqual([[widget]]);
    expect((wrapper.vm as any).isOpen).toBe(true);
    expect((wrapper.vm as any).addedWidgetIds.has('Widget.unique')).toBe(true);
  });

  it('closes without emitting select when the widget is missing from the store', () => {
    const wrapper = mountComponent();
    const warnSpy = jest.spyOn(console, 'warn').mockImplementation();

    (wrapper.vm as any).onSelect('Widget.missing');

    expect(wrapper.emitted().select).toBeUndefined();
    expect((wrapper.vm as any).isOpen).toBe(false);
    expect(warnSpy).toHaveBeenCalled();

    warnSpy.mockRestore();
  });

  it('resets the chosen category, hover and added widgets when the modal closes', () => {
    const wrapper = mountComponent();
    const vm = wrapper.vm as any;
    vm.chosenCategory = 'Visitors';
    vm.hoveredWidgetId = 'Widget.unique';
    vm.addedWidgetIds = new Set(['Widget.unique']);
    vm.existingWidgetIds = new Set(['Widget.placed']);

    vm.onClosed();

    expect(vm.chosenCategory).toBeNull();
    expect(vm.hoveredWidgetId).toBeNull();
    expect(vm.addedWidgetIds.size).toBe(0);
    expect(vm.existingWidgetIds.size).toBe(0);
  });

  it('unregisters Matomo listeners on unmount', () => {
    const wrapper = mountComponent();

    wrapper.unmount();

    expect(mockMatomo.off).toHaveBeenCalledWith('Dashboard.AddWidget.open', expect.any(Function));
  });
});
