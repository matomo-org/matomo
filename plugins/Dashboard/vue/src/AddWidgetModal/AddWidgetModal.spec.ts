/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { shallowMount } from '@vue/test-utils';

const mockWidgetPreview = jest.fn();
const mockRootJQuery = { widgetPreview: mockWidgetPreview, find: () => ({ length: 0 }) };
const mockDollar = jest.fn(() => mockRootJQuery);

const testWindow = window as any;
testWindow.$ = mockDollar;
testWindow.jQuery = mockDollar;
testWindow.widgetsHelper = {
  getWidgetObjectFromUniqueId: jest.fn(),
};

const mockMatomo = { on: jest.fn(), off: jest.fn() };

jest.mock('CoreHome', () => ({
  Matomo: mockMatomo,
  translate: (key: string) => key,
  WidgetType: {},
  MatomoModal: { template: '<div><slot /></div>' },
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
  });

  it('opens when the Matomo event fires', () => {
    const wrapper = mountComponent();

    getHandler('Dashboard.AddWidget.open')!();
    expect((wrapper.vm as any).isOpen).toBe(true);
  });

  it('closes immediately and emits select with the resolved widget', () => {
    const wrapper = mountComponent();
    (wrapper.vm as any).onOpened(document.createElement('div'));
    getHandler('Dashboard.AddWidget.open')!();

    const widget = { uniqueId: 'Widget.unique', parameters: { foo: 'bar' } };
    testWindow.widgetsHelper.getWidgetObjectFromUniqueId.mockImplementation(
      (_uniqueId: string, callback: (resolvedWidget: unknown) => void) => callback(widget),
    );

    mockWidgetPreview.mock.calls[0][0].onSelect('Widget.unique');

    expect((wrapper.vm as any).isOpen).toBe(false);
    expect(testWindow.widgetsHelper.getWidgetObjectFromUniqueId).toHaveBeenCalledWith(
      'Widget.unique',
      expect.any(Function),
    );
    expect(wrapper.emitted().select).toEqual([[widget]]);
  });

  it('closes without emitting select when the widget cannot be resolved', () => {
    const wrapper = mountComponent();
    (wrapper.vm as any).onOpened(document.createElement('div'));
    getHandler('Dashboard.AddWidget.open')!();

    testWindow.widgetsHelper.getWidgetObjectFromUniqueId.mockImplementation(
      (_uniqueId: string, callback: (resolvedWidget: unknown) => void) => callback(false),
    );

    mockWidgetPreview.mock.calls[0][0].onSelect('Widget.missing');

    expect((wrapper.vm as any).isOpen).toBe(false);
    expect(wrapper.emitted().select).toBeUndefined();
  });

  it('unregisters Matomo listeners on unmount', () => {
    const wrapper = mountComponent();

    wrapper.unmount();

    expect(mockMatomo.off).toHaveBeenCalledWith('Dashboard.AddWidget.open', expect.any(Function));
    expect(mockMatomo.off).toHaveBeenCalledWith('WidgetsStore.reloaded', expect.any(Function));
  });
});
