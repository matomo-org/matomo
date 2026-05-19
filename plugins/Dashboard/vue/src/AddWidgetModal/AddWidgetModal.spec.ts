/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { shallowMount } from '@vue/test-utils';

const mockModal = jest.fn();
const mockWidgetPreview = jest.fn();
const mockRootJQuery = { modal: mockModal, widgetPreview: mockWidgetPreview };
const mockDollar = jest.fn(() => mockRootJQuery);

const testWindow = window as any;
testWindow.$ = mockDollar;
testWindow.jQuery = mockDollar;
testWindow.widgetsHelper = { getWidgetObjectFromUniqueId: jest.fn() };

const mockMatomo = { on: jest.fn(), off: jest.fn() };

jest.mock('CoreHome', () => ({
  Matomo: mockMatomo,
  translate: (key: string) => key,
  WidgetType: {},
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const AddWidgetModal = require('./AddWidgetModal.vue').default;

describe('Dashboard/AddWidgetModal', () => {
  function mountComponent() {
    return shallowMount(AddWidgetModal as any);
  }

  beforeEach(() => {
    jest.clearAllMocks();
    mockModal.mockReturnValue(mockRootJQuery);
  });

  it('opens and closes the modal when Matomo events fire', () => {
    mountComponent();

    const openHandler = mockMatomo.on.mock.calls.find((call) => call[0] === 'Dashboard.AddWidget.open')?.[1];
    const closeHandler = mockMatomo.on.mock.calls.find((call) => call[0] === 'Dashboard.AddWidget.close')?.[1];

    openHandler();
    closeHandler();

    expect(mockModal).toHaveBeenCalledWith('open');
    expect(mockModal).toHaveBeenCalledWith('close');
  });

  it('emits select with the resolved widget when one is chosen', () => {
    const wrapper = mountComponent();
    mockModal.mock.calls[0][0].onOpenEnd();

    const widget = { uniqueId: 'Widget.unique', parameters: { foo: 'bar' } };
    testWindow.widgetsHelper.getWidgetObjectFromUniqueId.mockImplementation(
      (_uniqueId: string, callback: (resolvedWidget: unknown) => void) => callback(widget),
    );

    mockWidgetPreview.mock.calls[0][0].onSelect('Widget.unique');

    expect(wrapper.emitted().select).toEqual([[widget]]);
  });

  it('unregisters Matomo listeners on unmount', () => {
    const wrapper = mountComponent();

    wrapper.unmount();

    expect(mockMatomo.off).toHaveBeenCalledWith('Dashboard.AddWidget.open', expect.any(Function));
    expect(mockMatomo.off).toHaveBeenCalledWith('Dashboard.AddWidget.close', expect.any(Function));
    expect(mockMatomo.off).toHaveBeenCalledWith('WidgetsStore.reloaded', expect.any(Function));
  });
});
