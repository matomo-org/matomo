/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { shallowMount } from '@vue/test-utils';

const mockModal = jest.fn();
const mockWidgetPreview = jest.fn();
const mockFind = jest.fn(() => ({ length: 0 }));
const mockRootJQuery = {
  modal: mockModal,
  widgetPreview: mockWidgetPreview,
  find: mockFind,
};
const mockDollar = jest.fn(() => mockRootJQuery);

const testWindow = window as any;
testWindow.$ = mockDollar;
testWindow.jQuery = mockDollar;
testWindow.widgetsHelper = {
  getWidgetObjectFromUniqueId: jest.fn(),
};

const mockMatomo = {
  on: jest.fn(),
  off: jest.fn(),
};
const mockTranslate = jest.fn((key: string) => key);

jest.mock('CoreHome', () => ({
  Matomo: mockMatomo,
  translate: mockTranslate,
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

  it('renders the translated modal title and initialises the modal lifecycle', () => {
    const wrapper = mountComponent();

    expect(mockTranslate).toHaveBeenCalledWith('Dashboard_AddAWidget');
    expect(wrapper.html()).toContain('Dashboard_AddAWidget');
    expect(mockModal).toHaveBeenCalledWith({
      dismissible: true,
      onOpenEnd: expect.any(Function),
      onCloseEnd: expect.any(Function),
    });
  });

  it('registers and unregisters modal-related Matomo event handlers', () => {
    const wrapper = mountComponent();

    expect(mockMatomo.on).toHaveBeenCalledWith('Dashboard.AddWidget.open', expect.any(Function));
    expect(mockMatomo.on).toHaveBeenCalledWith('Dashboard.AddWidget.close', expect.any(Function));
    expect(mockMatomo.on).toHaveBeenCalledWith('WidgetsStore.reloaded', expect.any(Function));

    wrapper.unmount();

    expect(mockMatomo.off).toHaveBeenCalledWith('Dashboard.AddWidget.open', expect.any(Function));
    expect(mockMatomo.off).toHaveBeenCalledWith('Dashboard.AddWidget.close', expect.any(Function));
    expect(mockMatomo.off).toHaveBeenCalledWith('WidgetsStore.reloaded', expect.any(Function));
  });

  it('opens when the open event fires and closes when the close event fires', () => {
    mountComponent();

    const openHandler = mockMatomo.on.mock.calls.find((call) => call[0] === 'Dashboard.AddWidget.open')?.[1];
    const closeHandler = mockMatomo.on.mock.calls.find((call) => call[0] === 'Dashboard.AddWidget.close')?.[1];

    openHandler();
    closeHandler();

    expect(mockModal).toHaveBeenCalledWith('open');
    expect(mockModal).toHaveBeenCalledWith('close');
  });

  it('rebuilds the preview when widgets reload while the modal is open', () => {
    mountComponent();

    const modalOptions = mockModal.mock.calls[0][0];
    const reloadHandler = mockMatomo.on.mock.calls.find((call) => call[0] === 'WidgetsStore.reloaded')?.[1];

    reloadHandler();
    expect(mockWidgetPreview).not.toHaveBeenCalled();

    modalOptions.onOpenEnd();
    expect(mockWidgetPreview).toHaveBeenCalledWith({
      isWidgetAvailable: expect.any(Function),
      onSelect: expect.any(Function),
      resetOnSelect: true,
    });

    mockWidgetPreview.mockClear();
    reloadHandler();

    expect(mockWidgetPreview).toHaveBeenCalledWith({
      isWidgetAvailable: expect.any(Function),
      onSelect: expect.any(Function),
      resetOnSelect: true,
    });

    modalOptions.onCloseEnd();
    expect(mockWidgetPreview).toHaveBeenCalledWith('reset');
  });

  it('passes a widget-availability callback that checks the dashboard area', () => {
    mountComponent();

    const modalOptions = mockModal.mock.calls[0][0];
    modalOptions.onOpenEnd();

    const widgetPreviewOptions = mockWidgetPreview.mock.calls[0][0];

    mockFind.mockReturnValueOnce({ length: 0 });
    expect(widgetPreviewOptions.isWidgetAvailable('Widget.available')).toBe(true);
    expect(mockFind).toHaveBeenLastCalledWith('[widgetId="Widget.available"]');

    mockFind.mockReturnValueOnce({ length: 1 });
    expect(widgetPreviewOptions.isWidgetAvailable('Widget.taken')).toBe(false);
    expect(mockFind).toHaveBeenLastCalledWith('[widgetId="Widget.taken"]');
  });

  it('emits select and closes after a widget is resolved', () => {
    const wrapper = mountComponent();
    const modalOptions = mockModal.mock.calls[0][0];
    modalOptions.onOpenEnd();

    const widget = { uniqueId: 'Widget.unique', parameters: { foo: 'bar' } };
    testWindow.widgetsHelper.getWidgetObjectFromUniqueId.mockImplementation(
      (_uniqueId: string, callback: (resolvedWidget: unknown) => void) => callback(widget),
    );

    const widgetPreviewOptions = mockWidgetPreview.mock.calls[0][0];
    widgetPreviewOptions.onSelect('Widget.unique');

    expect(testWindow.widgetsHelper.getWidgetObjectFromUniqueId).toHaveBeenCalledWith(
      'Widget.unique',
      expect.any(Function),
    );
    expect(wrapper.emitted().select).toEqual([[widget]]);
    expect(mockModal).toHaveBeenCalledWith('close');
  });
});
