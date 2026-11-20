/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Covers the DOM adjacency the jQuery-side report action handlers resolve through, the twin of
 * reportScope.ts. This one decides which element a handler rebinds its controls within, so getting
 * it wrong makes one report drive another's controls - and it is reachable today, unlike the Vue
 * side. The prototype methods are pure DOM traversal, so they are called unbound rather than
 * through a constructed DataTable, which would need the whole UIControl lifecycle.
 */

// The methods under test are named by the jQuery-era file that owns them.
/* eslint-disable no-underscore-dangle */
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

const { $ } = window;

interface ReportScopeHost {
  param: { idSubtable?: string };
  parentId?: string;
  _findReportScope: (domElem: JQuery) => JQuery;
  _findReportHeaderApp: (domElem: JQuery) => { $el: JQuery, app: unknown } | null;
}

let prototype: ReportScopeHost;

beforeAll(() => {
  const uiExports: Record<string, unknown> = { UIControl: function UIControl() {} };

  // The file is an IIFE taking ($, require). Importing it would bind `require` to the test runner's
  // module loader, which has no 'piwik/UI', so evaluate it with both dependencies passed in.
  const source = readFileSync(
    resolve(process.cwd(), 'plugins/CoreHome/javascripts/dataTable.js'),
    'utf8',
  );
  // eslint-disable-next-line no-new-func
  new Function('jQuery', 'require', 'piwikHelper', source)($, () => uiExports, window.piwikHelper);

  prototype = (uiExports.DataTable as { prototype: ReportScopeHost }).prototype;
});

afterEach(() => {
  document.body.innerHTML = '';
});

// The methods call each other through `this`, so the fixture instance inherits from the prototype
// rather than carrying copies of them.
function instance(host: Partial<ReportScopeHost> = {}): ReportScopeHost {
  return Object.assign(Object.create(prototype), { param: {} }, host);
}

function scopeOf(tableId: string, host: Partial<ReportScopeHost> = {}): JQuery {
  return instance(host)._findReportScope($(`#${tableId}`));
}

describe('DataTable._findReportScope', () => {
  it('should widen to the wrapper holding the header and the table', () => {
    document.body.innerHTML = `
      <div class="pageWrap">
        <div class="card-content" id="wrapper">
          <div vue-entry="CoreHome.ReportHeader"></div>
          <div class="dataTable" id="report"></div>
        </div>
      </div>`;

    expect(scopeOf('report')[0]).toBe(document.querySelector('#wrapper'));
  });

  // An empty titled report gets an extra `.card > .card-content` between header and table, so the
  // header is not the table's own previous sibling.
  it('should climb to the wrapper of an empty report shown in a card', () => {
    document.body.innerHTML = `
      <div id="wrapper">
        <div vue-entry="CoreHome.ReportHeader"></div>
        <div class="card">
          <div class="card-content">
            <div class="dataTable isDataTableEmpty" id="report"></div>
          </div>
        </div>
      </div>`;

    expect(scopeOf('report')[0]).toBe(document.querySelector('#wrapper'));
  });

  it('should stay on the table when no header is adjacent', () => {
    document.body.innerHTML = `
      <div class="card-content" id="wrapper">
        <div class="dataTable" id="report"></div>
      </div>`;

    expect(scopeOf('report')[0]).toBe(document.querySelector('#report'));
  });

  it('should widen to the widget when the header sits in the widget chrome', () => {
    document.body.innerHTML = `
      <div class="widget" id="widget">
        <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader"></div></div>
        <div class="widgetContent"><div class="dataTable" id="report"></div></div>
      </div>`;

    expect(scopeOf('report')[0]).toBe(document.querySelector('#widget'));
  });

  // Every handler rebinds with `.off('click.reportAction')`, so widening to a widget that pairs two
  // reports would let whichever instance loads last take over the other's controls.
  it('should stay on the table when the widget holds more than one report', () => {
    document.body.innerHTML = `
      <div class="widget" id="widget">
        <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader"></div></div>
        <div class="widgetContent">
          <div class="dataTable" id="report"></div>
          <div class="dataTable" id="sibling"></div>
        </div>
      </div>`;

    expect(scopeOf('report')[0]).toBe(document.querySelector('#report'));
  });

  it('should not widen past an expanded subtable of its own report', () => {
    document.body.innerHTML = `
      <div class="card-content" id="wrapper">
        <div vue-entry="CoreHome.ReportHeader"></div>
        <div class="dataTable" id="report">
          <div class="dataTable subDataTable" id="subtable"></div>
        </div>
      </div>`;

    expect(scopeOf('report')[0]).toBe(document.querySelector('#wrapper'));
  });

  // Subtables reuse the parent's header and have no controls of their own. Both signals matter:
  // `parentId` is only ever set by ActionsDataTable, a generic expandable subtable is a fresh
  // instance carrying `idSubtable` in its params.
  it('should keep a generic subtable on its own table', () => {
    document.body.innerHTML = `
      <div class="card-content">
        <div vue-entry="CoreHome.ReportHeader"></div>
        <div class="dataTable" id="report"></div>
      </div>`;

    expect(scopeOf('report', { param: { idSubtable: '3' } })[0])
      .toBe(document.querySelector('#report'));
  });

  it('should keep an actions subtable on its own table', () => {
    document.body.innerHTML = `
      <div class="card-content">
        <div vue-entry="CoreHome.ReportHeader"></div>
        <div class="dataTable" id="report"></div>
      </div>`;

    expect(scopeOf('report', { parentId: 'subDataTable_7' })[0])
      .toBe(document.querySelector('#report'));
  });
});

describe('DataTable._findReportHeaderApp', () => {
  function headerOf(tableId: string) {
    return instance()._findReportHeaderApp($(`#${tableId}`));
  }

  it('should find the header of a full-page report', () => {
    document.body.innerHTML = `
      <div class="card-content">
        <div vue-entry="CoreHome.ReportHeader" id="header"></div>
        <div class="dataTable" id="report"></div>
      </div>`;

    expect(headerOf('report')!.$el[0]).toBe(document.querySelector('#header'));
  });

  // The shape _findReportScope() already climbed for. While this returned null here the report kept
  // its widened controls but silently lost its search, which is bridged through the header app.
  it('should find the header of an empty report shown in a card', () => {
    document.body.innerHTML = `
      <div>
        <div vue-entry="CoreHome.ReportHeader" id="header"></div>
        <div class="card">
          <div class="card-content">
            <div class="dataTable isDataTableEmpty" id="report"></div>
          </div>
        </div>
      </div>`;

    expect(headerOf('report')!.$el[0]).toBe(document.querySelector('#header'));
  });

  it('should find the header in the widget chrome', () => {
    document.body.innerHTML = `
      <div class="widget">
        <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader" id="header"></div></div>
        <div class="widgetContent"><div class="dataTable" id="report"></div></div>
      </div>`;

    expect(headerOf('report')!.$el[0]).toBe(document.querySelector('#header'));
  });

  it('should return null for a report with no header', () => {
    document.body.innerHTML = '<div class="card-content"><div class="dataTable" id="report"></div></div>';

    expect(headerOf('report')).toBe(null);
  });
});
