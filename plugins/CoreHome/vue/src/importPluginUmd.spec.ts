/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import importPluginUmd from './importPluginUmd';

const pluginWindow = window as unknown as Record<string, unknown>;

describe('CoreHome/importPluginUmd', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
    document.head.querySelectorAll('script').forEach((script) => script.remove());
  });

  function findScript(plugin: string): HTMLScriptElement {
    return document.head.querySelector(`script[src$="plugin=${plugin}"]`) as HTMLScriptElement;
  }

  it('should resolve a plugin whose script takes longer than a few seconds to load', async () => {
    const plugin = 'SlowTestPlugin';
    let outcome: unknown = 'pending';
    importPluginUmd(plugin).then((value) => {
      outcome = value;
    }, (error) => {
      outcome = error;
    });

    await vi.advanceTimersByTimeAsync(5000);
    expect(outcome).toBe('pending');

    const pluginObject = { name: plugin };
    pluginWindow[plugin] = pluginObject;
    findScript(plugin).dispatchEvent(new Event('load'));
    await vi.advanceTimersByTimeAsync(100);

    expect(outcome).toBe(pluginObject);
    delete pluginWindow[plugin];
  });

  it('should reject once the load timeout passes without the plugin appearing', async () => {
    const plugin = 'MissingTestPlugin';
    let settled = false;
    const promise = importPluginUmd(plugin);
    promise.catch(() => {
      settled = true;
    });
    const rejected = expect(promise).rejects.toMatchObject({
      name: 'PluginOnDemandLoadError',
      type: 'timeout',
    });

    await vi.advanceTimersByTimeAsync(119000);
    expect(settled).toBe(false);

    await vi.advanceTimersByTimeAsync(1000 + 1100);
    await rejected;
  });
});
