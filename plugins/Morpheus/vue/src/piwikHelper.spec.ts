/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('piwikHelper', () => {
  afterEach(() => {
    document.body.innerHTML = '';
    delete (window as any).CoreHome;
    delete (window as any).MyPlugin;
    (window.piwik as any).pluginsToLoadOnDemand = [];
  });

  it('should pass a component name to createVueApp for vue-entry components', () => {
    const app = {
      component: vi.fn(),
      mount: vi.fn(() => ({})),
      unmount: vi.fn(),
    };

    const createVueApp = vi.fn(() => app);

    (window as any).CoreHome = {
      createVueApp,
      useExternalPluginComponent: vi.fn(),
    };
    (window as any).MyPlugin = {
      MyComponent: {},
    };
    (window.piwik as any).pluginsToLoadOnDemand = [];

    document.body.innerHTML = '<div id="root"><div vue-entry="MyPlugin.MyComponent"></div></div>';

    window.piwikHelper.compileVueEntryComponents('#root');

    expect(createVueApp).toHaveBeenCalledTimes(1);
    expect(createVueApp).toHaveBeenCalledWith(expect.objectContaining({
      name: 'MyPlugin.MyComponent',
    }));
  });
});
