/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Covers the popover mount point in javascripts/transitions.js. That file is a classic script that
 * defines globals, so it is evaluated in the window scope rather than imported.
 */

import fs from 'node:fs';
import path from 'node:path';

declare global {
  // eslint-disable-next-line
  var DataTable_RowActions_Transitions: any;
}

const ROOT = path.resolve(__dirname, '../../../../..');

function loadScript(relativePath: string) {
  window.eval(fs.readFileSync(path.join(ROOT, relativePath), 'utf8'));
}

describe('Transitions/popover mount point', () => {
  let compiled: HTMLElement[];
  let destroyed: HTMLElement[];
  let listeners: Record<string, ((params: unknown) => void)[]>;

  beforeAll(() => {
    const globals = window as unknown as Record<string, unknown>;

    // Globals transitions.js expects to already exist when it registers its row action.
    globals.DataTable_RowAction = function DataTableRowAction() { /* base class */ };
    globals.DataTable_RowActions_Registry = { register: () => undefined };
    globals._pk_translate = (key: string) => key;
    globals._pk_externalRawLink = (url: string) => url;
    globals.sprintf = (template: string, ...args: string[]) => {
      let index = 0;
      return template.replace(/%(\d+\$)?s/g, () => {
        const value = args[index];
        index += 1;
        return value;
      });
    };
    globals.piwik = { ...(globals.piwik as object), config: {} };
    // Piwik_Popover's close handler reaches for these; jsdom provides neither.
    globals.require = () => ({ UIControl: { cleanupUnusedControls: () => undefined } });
    globals.scrollTo = () => undefined;
    globals.isEscapeKey = () => false;

    loadScript('plugins/CoreHome/javascripts/popover.js');
    loadScript('plugins/Transitions/javascripts/transitions.js');
  });

  beforeEach(() => {
    compiled = [];
    destroyed = [];
    listeners = {};

    const globals = window as unknown as Record<string, unknown>;

    globals.translations = {
      General_LoadingPopover: 'Loading %s',
      General_LoadingPopoverFor: 'Loading %s',
    };

    globals.CoreHome = {
      Matomo: {
        on: (name: string, callback: (params: unknown) => void) => {
          listeners[name] = listeners[name] || [];
          listeners[name].push(callback);
        },
        off: (name: string, callback: (params: unknown) => void) => {
          listeners[name] = (listeners[name] || []).filter((entry) => entry !== callback);
        },
        postEvent: () => undefined,
      },
    };

    const helper = globals.piwikHelper as Record<string, unknown>;
    helper.compileVueEntryComponents = (element: HTMLElement) => {
      compiled.push(element);
    };
    helper.destroyVueComponent = (element: HTMLElement) => {
      destroyed.push(element);
    };
  });

  afterEach(() => {
    if (window.Piwik_Popover.isOpen()) {
      window.Piwik_Popover.close();
    }
  });

  function openPopover(overrideParams: Record<string, string> = {}) {
    const rowAction = new DataTable_RowActions_Transitions({ param: {} });
    rowAction.openPopover = vi.fn();
    rowAction.openTransitionsPopover('url', 'http://example.org/page', overrideParams);
    return rowAction;
  }

  /** The wrapper transitions.js retains, i.e. the only child of the popover container. */
  function wrapperElement(): HTMLElement {
    return document.querySelector('#Piwik_Popover > .transitionsPopover') as HTMLElement;
  }

  it('should mount the report through a vue-entry carrying the full context', () => {
    openPopover({ segment: 'actions>=1', date: '2012-08-09', period: 'year', idSite: '2' });

    const entry = wrapperElement().querySelector('[vue-entry]') as HTMLElement;
    expect(entry.getAttribute('vue-entry')).toBe('Transitions.TransitionsReport');
    expect(entry.getAttribute('action-type')).toBe('url');
    // JSON-encoded, so the parse compileVueEntryComponents runs on it is a round trip.
    expect(JSON.parse(entry.getAttribute('action-name')!)).toBe('http://example.org/page');
    expect(entry.getAttribute('context')).toBe('popover');
    expect(JSON.parse(entry.getAttribute('override-params')!)).toEqual({
      segment: 'actions>=1',
      date: '2012-08-09',
      period: 'year',
      idSite: '2',
    });
  });

  it('should not let a JSON-shaped action name be coerced by the vue-entry parser', () => {
    const rowAction = new DataTable_RowActions_Transitions({ param: {} });
    rowAction.openPopover = vi.fn();
    // A page title can be anything, including something that parses as a JSON number. '2024.10'
    // would arrive as 2024.1, i.e. a different action than the one that was clicked.
    rowAction.openTransitionsPopover('title', '2024.10', {});

    const entry = wrapperElement().querySelector('[vue-entry]') as HTMLElement;
    expect(JSON.parse(entry.getAttribute('action-name')!)).toBe('2024.10');
  });

  it('should keep the export control in the popover', () => {
    openPopover();

    const exporter = wrapperElement().querySelector('[vue-entry="Transitions.TransitionExporterLink"]');
    expect(exporter).not.toBeNull();
  });

  it('should hide the export control until the report has data', () => {
    openPopover();

    // Nothing to export while the popover is still loading, and showing the control next to the
    // loading message makes the popover look half-built.
    const control = wrapperElement().querySelector('.dataTableWrapper') as HTMLElement;
    expect(control.style.display).toBe('none');

    listeners['Transitions.dataChanged'][0]({ actionType: 'url', actionName: 'http://example.org/' });

    expect(control.style.display).toBe('');
  });

  it('should drop the dataChanged listener when the popover closes', () => {
    openPopover();
    expect(listeners['Transitions.dataChanged']).toHaveLength(1);

    window.Piwik_Popover.close();

    expect(listeners['Transitions.dataChanged']).toHaveLength(0);
  });

  it('should escape an action name that contains markup', () => {
    const rowAction = new DataTable_RowActions_Transitions({ param: {} });
    rowAction.openPopover = vi.fn();
    rowAction.openTransitionsPopover('url', 'http://example.org/<script>x()</script>', {});

    const entry = wrapperElement().querySelector('[vue-entry]') as HTMLElement;
    expect(JSON.parse(entry.getAttribute('action-name')!))
      .toBe('http://example.org/<script>x()</script>');
    expect(wrapperElement().querySelector('script')).toBeNull();
  });

  it('should rely on setContent to compile, and not compile the entry a second time', () => {
    openPopover();

    // showLoading() compiles its own loading content first; the entry itself is compiled exactly
    // once, by the setContent() call that installs the wrapper.
    const withEntries = compiled.filter((element) => element.querySelector('[vue-entry]'));
    expect(withEntries).toEqual([wrapperElement()]);
  });

  it('should destroy the component on the retained wrapper before the container is wiped', () => {
    openPopover();
    const wrapper = wrapperElement();

    let childrenWhenDestroyed = -1;
    (window.piwikHelper as unknown as Record<string, unknown>).destroyVueComponent = (
      element: HTMLElement,
    ) => {
      destroyed.push(element);
      childrenWhenDestroyed = (document.querySelector('#Piwik_Popover') as HTMLElement)
        .children.length;
    };

    window.Piwik_Popover.close();

    expect(destroyed).toEqual([wrapper]);
    // The wrapper was still in the container, i.e. the destroy beat the innerHTML wipe.
    expect(childrenWhenDestroyed).toBe(1);
    expect(document.querySelector('#Piwik_Popover')).toBeNull();
  });

  it('should destroy the previous component when the popover content is replaced', () => {
    openPopover();
    const first = wrapperElement();

    openPopover();

    expect(destroyed).toEqual([first]);
    expect(wrapperElement()).not.toBe(first);
  });

  it('should destroy only once when the content is replaced and the popover then closes', () => {
    openPopover();
    const first = wrapperElement();
    openPopover();
    const second = wrapperElement();

    window.Piwik_Popover.close();

    expect(destroyed).toEqual([first, second]);
  });

  it('should release its reloadPopover listener when the popover closes', () => {
    openPopover();
    expect(listeners['Transitions.reloadPopover']).toHaveLength(1);

    window.Piwik_Popover.close();

    expect(listeners['Transitions.reloadPopover']).toHaveLength(0);
  });

  it('should reopen the popover for a page the report navigated to', () => {
    const rowAction = openPopover({ segment: 'actions>=1' });

    listeners['Transitions.reloadPopover'][0]({ url: 'example.org/next' });

    expect(rowAction.openPopover).toHaveBeenCalledWith(
      'segment:actions%3E%3D1:url:http://example.org/next',
    );
  });

  it('should ignore a reload without a url', () => {
    const rowAction = openPopover();

    listeners['Transitions.reloadPopover'][0]({});

    expect(rowAction.openPopover).not.toHaveBeenCalled();
  });
});
