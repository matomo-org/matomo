/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import reportIdentity from './reportIdentity';

function build(html: string): HTMLElement {
  const host = document.createElement('div');
  host.innerHTML = html;
  document.body.appendChild(host);
  return host.querySelector('.target') as HTMLElement;
}

describe('CoreHome/reportIdentity', () => {
  afterEach(() => {
    document.body.innerHTML = '';
  });

  it('returns nothing without an element', () => {
    expect(reportIdentity(null)).toBe('');
    expect(reportIdentity(undefined)).toBe('');
  });

  it('keys off the widget id when there is one', () => {
    const el = build('<div widgetId="widgetX"><div class="target"></div></div>');
    expect(reportIdentity(el)).toBe('widget:widgetX');
  });

  it('falls back to the unique id Widget.vue puts on a container child', () => {
    const el = build('<div class="matomo-widget" id="uniq7"><div class="target"></div></div>');
    expect(reportIdentity(el)).toBe('widget:uniq7');
  });

  it('keys off the report otherwise, container included', () => {
    const el = build(
      '<div containerid="VisitOverview"><div data-report="Actions.getPageUrls">'
      + '<div class="target"></div></div></div>',
    );
    expect(reportIdentity(el)).toBe('report:VisitOverview:Actions.getPageUrls');
  });

  it('takes the report id from the caller, which the header has and the DOM around it does not', () => {
    const el = build('<div><div class="target"></div></div>');
    expect(reportIdentity(el, 'Actions.getEntryPageUrls'))
      .toBe('report:Actions.getEntryPageUrls');
  });

  it('prefers the caller\'s report id over the one in the DOM', () => {
    const el = build('<div data-report="Actions.getPageUrls"><div class="target"></div></div>');
    expect(reportIdentity(el, 'Actions.getEntryPageUrls'))
      .toBe('report:Actions.getEntryPageUrls');
  });

  it('returns nothing when no report can be named', () => {
    const el = build('<div><div class="target"></div></div>');
    expect(reportIdentity(el)).toBe('');
  });

  it.each([
    ['a widget', '<div widgetId="widgetX"><div class="target"></div></div>', 'dialog:widget:widgetX'],
    ['a report', '<div data-report="R"><div class="target"></div></div>', 'dialog:report:R'],
  ])('marks %s inside a dialog, so a popover does not read the page behind it', (
    _name: string,
    inner: string,
    expected: string,
  ) => {
    const el = build(`<div class="ui-dialog">${inner}</div>`);
    expect(reportIdentity(el)).toBe(expected);
  });
});
