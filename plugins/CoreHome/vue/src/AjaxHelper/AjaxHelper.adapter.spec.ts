/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const NUL = String.fromCharCode(0);

// Capture jQuery's original implementation before the adapter installs its wrapper.
const originalParam = window.$.param;

describe('CoreHome/AjaxHelper.adapter', () => {
  beforeAll(async () => {
    await import('./AjaxHelper.adapter');
  });

  afterAll(() => {
    (window.$ as JQueryStatic & { param: typeof window.$.param }).param = originalParam;
  });

  it('keeps supported parameter names', () => {
    expect(window.$.param({ idSite: '1', period: 'day' })).toBe('idSite=1&period=day');
  });

  it('drops an unsupported parameter name', () => {
    expect(window.$.param({ foo: 'one', [`foo${NUL}x`]: 'two' })).toBe('foo=one');
  });

  it('validates parameter names generated for nested values', () => {
    expect(window.$.param({ settingValues: { 'a.b': '1', ok: '2' } }))
      .toBe('settingValues%5Ba.b%5D=1&settingValues%5Bok%5D=2');
    expect(window.$.param({ settingValues: { [`a${NUL}b`]: '1', ok: '2' } }))
      .toBe('settingValues%5Bok%5D=2');
  });

  it('keeps the array names Matomo relies on', () => {
    expect(window.$.param({ columns: ['nb_visits', 'nb_actions'] }))
      .toBe('columns%5B%5D=nb_visits&columns%5B%5D=nb_actions');
    expect(window.$.param({ settingValues: { Live: [{ name: 'x' }] } }))
      .toBe('settingValues%5BLive%5D%5B0%5D%5Bname%5D=x');
  });

  it('keeps an array under a name the server rewrites', () => {
    expect(window.$.param({ 'a.b': ['one', 'two'] }))
      .toBe('a.b%5B%5D=one&a.b%5B%5D=two');
  });

  it('validates data serialized by jQuery', () => {
    let serializedData: string|undefined;

    window.$.ajax({
      url: '/index.php',
      method: 'POST',
      data: { a: '1', [`a${NUL}x`]: '2' },
      beforeSend: (jqXHR: JQuery.jqXHR, settings: JQuery.AjaxSettings) => {
        serializedData = settings.data as string;
        return false;
      },
    });

    expect(serializedData).toBe('a=1');
  });
});
