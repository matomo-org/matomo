/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const mockBroadcast = { getValueFromUrl: jest.fn((_param: string) => '') };
const mockMatomo = { on: jest.fn(), off: jest.fn(), broadcast: mockBroadcast };

jest.mock('CoreHome', () => ({
  Matomo: mockMatomo,
  translate: (key: string) => key,
  Widget: { template: '<div class="stub-widget" />' },
  WidgetType: {},
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const WidgetPreview = require('./WidgetPreview.vue').default;

describe('Dashboard/AddWidgetModal/WidgetPreview', () => {
  function getHandler(eventName: string) {
    return mockMatomo.on.mock.calls.find((call) => call[0] === eventName)?.[1];
  }

  function mountComponent() {
    return mount(WidgetPreview as any, {
      props: { widget: { uniqueId: 'W1' } },
      attachTo: document.body,
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    mockBroadcast.getValueFromUrl.mockReturnValue('');
    // Make sure no previous test leaked a body#standalone marker.
    document.body.removeAttribute('id');
  });

  afterEach(() => {
    document.body.removeAttribute('id');
  });

  it('registers the widget:loaded handler on mount and removes it on unmount', () => {
    const wrapper = mountComponent();

    expect(mockMatomo.on).toHaveBeenCalledWith('widget:loaded', expect.any(Function));

    wrapper.unmount();

    expect(mockMatomo.off).toHaveBeenCalledWith('widget:loaded', expect.any(Function));
  });

  it('forces widget=1 and showtitle=0 and does NOT set disableLink on a normal dashboard page', () => {
    // Regression guards:
    //  - `widget=1` matches legacy widgetMenu.js so the server renders in widgetized layout.
    //  - `showtitle=0` suppresses the bare <h2> the server would otherwise emit via
    //    _dataTable.twig `showOnlyTitleWithoutCard`, which duplicated the modal's own
    //    "Widget preview" header above the preview pane.
    //  - `disableLink` is only forced on Widgetize embeds or body#standalone (legacy
    //    parity). On a normal dashboard page the preview must match the widget the user
    //    actually adds, so links stay enabled (disableLink absent / 0).
    const incoming = { uniqueId: 'W1', parameters: { module: 'X', action: 'y' } };
    const wrapper = mount(WidgetPreview as any, {
      props: { widget: incoming },
      attachTo: document.body,
    });

    const { previewWidget } = wrapper.vm as unknown as {
      previewWidget: { uniqueId: string; parameters: Record<string, unknown> };
    };
    expect(previewWidget.parameters).toEqual({
      module: 'X',
      action: 'y',
      widget: '1',
      showtitle: '0',
    });
    expect(previewWidget.parameters).not.toHaveProperty('disableLink');
    // Guard against mutating the prop: the dashboard's persisted metadata must
    // not pick up the preview-only flags when the user actually adds the widget.
    expect(incoming.parameters).toEqual({ module: 'X', action: 'y' });

    wrapper.unmount();
  });

  it('forces disableLink=1 when the URL already carries disableLink (Widgetize embed)', () => {
    mockBroadcast.getValueFromUrl.mockImplementation((param: string) => (
      param === 'disableLink' ? '1' : ''
    ));
    const wrapper = mount(WidgetPreview as any, {
      props: { widget: { uniqueId: 'W1', parameters: {} } },
      attachTo: document.body,
    });

    const { previewWidget } = wrapper.vm as unknown as {
      previewWidget: { parameters: Record<string, unknown> };
    };
    expect(previewWidget.parameters.disableLink).toBe('1');
    expect(mockBroadcast.getValueFromUrl).toHaveBeenCalledWith('disableLink');

    wrapper.unmount();
  });

  it('forces disableLink=1 when the page is body#standalone', () => {
    document.body.id = 'standalone';
    const wrapper = mount(WidgetPreview as any, {
      props: { widget: { uniqueId: 'W1', parameters: {} } },
      attachTo: document.body,
    });

    const { previewWidget } = wrapper.vm as unknown as {
      previewWidget: { parameters: Record<string, unknown> };
    };
    expect(previewWidget.parameters.disableLink).toBe('1');

    wrapper.unmount();
  });

  it('triggers widget:create on the preview .widgetContent for the previewed widget', () => {
    const wrapper = mountComponent();
    const root = wrapper.element as HTMLElement;
    const widgetEl = root.querySelector('.widget') as HTMLElement;
    const widgetContent = root.querySelector('.widgetContent') as HTMLElement;
    const created: Array<{ element: JQuery }> = [];
    (window as any).$(widgetContent).on('widget:create', (_evt: Event, w: { element: JQuery }) => {
      created.push(w);
    });

    getHandler('widget:loaded')!({
      parameters: { uniqueId: 'W1' },
      element: (window as any).$(widgetContent),
    });

    expect(created).toHaveLength(1);
    expect(created[0].element[0]).toBe(widgetEl);

    wrapper.unmount();
  });

  it('ignores widget:loaded events whose element is outside the preview', () => {
    const wrapper = mountComponent();
    const root = wrapper.element as HTMLElement;
    const widgetContent = root.querySelector('.widgetContent') as HTMLElement;
    const created = jest.fn();
    (window as any).$(widgetContent).on('widget:create', created);

    const detached = document.createElement('div');
    document.body.appendChild(detached);

    getHandler('widget:loaded')!({
      parameters: { uniqueId: 'W1' },
      element: (window as any).$(detached),
    });

    expect(created).not.toHaveBeenCalled();

    document.body.removeChild(detached);
    wrapper.unmount();
  });

  it('ignores widget:loaded events whose uniqueId does not match the previewed widget', () => {
    const wrapper = mountComponent();
    const root = wrapper.element as HTMLElement;
    const widgetContent = root.querySelector('.widgetContent') as HTMLElement;
    const created = jest.fn();
    (window as any).$(widgetContent).on('widget:create', created);

    getHandler('widget:loaded')!({
      parameters: { uniqueId: 'NestedSubWidget' },
      element: (window as any).$(widgetContent),
    });

    expect(created).not.toHaveBeenCalled();

    wrapper.unmount();
  });
});
