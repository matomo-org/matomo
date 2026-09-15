/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import findReportRoot from './reportScope';

// The fallback branch is unreachable until the action bar actually moves into the header, so these
// fixtures are the only thing exercising it. They reproduce the shapes _dataTable.twig can emit.
describe('findReportRoot', () => {
  afterEach(() => {
    document.body.innerHTML = '';
  });

  function control(id: string): HTMLElement {
    return document.querySelector(`#${id}`) as HTMLElement;
  }

  it('should return the enclosing table when the control sits inside it', () => {
    document.body.innerHTML = `
      <div class="card-content">
        <div vue-entry="CoreHome.ReportHeader"></div>
        <div class="dataTable" id="report"><a id="icon"></a></div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#report'));
  });

  it('should reach the table from a control in the header of a card report', () => {
    document.body.innerHTML = `
      <div class="card-content">
        <div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div>
        <div class="dataTable" id="report"></div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#report'));
  });

  // A titled report shown without a card wraps header and table in a bare `div`, so a resolver
  // keyed on known wrapper classes rather than on containment misses this shape entirely.
  it('should reach the table through an unclassed wrapper', () => {
    document.body.innerHTML = `
      <div class="pageWrap">
        <div>
          <div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div>
          <div class="dataTable" id="report"></div>
        </div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#report'));
  });

  // An empty titled report gets an extra `.card > .card-content` between the wrapper and the table,
  // so the table is not the header's own next sibling.
  it('should reach the table of an empty report wrapped in a card', () => {
    document.body.innerHTML = `
      <div>
        <div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div>
        <div class="card">
          <div class="card-content">
            <div class="dataTable isDataTableEmpty" id="report"></div>
          </div>
        </div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#report'));
  });

  it('should reach the table from a control in the widget chrome', () => {
    document.body.innerHTML = `
      <div class="widget">
        <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div></div>
        <div class="widgetContent"><div class="dataTable" id="report"></div></div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#report'));
  });

  it('should resolve to the report it belongs to, not a sibling report', () => {
    document.body.innerHTML = `
      <div class="pageWrap">
        <div class="card-content">
          <div vue-entry="CoreHome.ReportHeader"></div>
          <div class="dataTable" id="first"></div>
        </div>
        <div class="card-content">
          <div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div>
          <div class="dataTable" id="second"></div>
        </div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#second'));
  });

  it('should return the outer table for a header control of a report with an expanded subtable', () => {
    document.body.innerHTML = `
      <div class="card-content">
        <div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div>
        <div class="dataTable" id="outer">
          <div class="dataTable subDataTable" id="inner"></div>
        </div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#outer'));
  });

  it('should keep a control inside a subtable bound to that subtable', () => {
    document.body.innerHTML = `
      <div class="card-content">
        <div class="dataTable" id="outer">
          <div class="dataTable subDataTable" id="inner"><a id="icon"></a></div>
        </div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#inner'));
  });

  // A widget renders its header before its table, so between the two there is a window where the
  // control exists and its own report does not. Climbing past the report then lands on whatever
  // wrapper holds the column, whose first table belongs to the *next* widget down.
  it('should not resolve to a neighbouring report while its own report has no table yet', () => {
    document.body.innerHTML = `
      <div class="col">
        <div class="widget" id="loading">
          <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div></div>
          <div class="widgetContent"></div>
        </div>
        <div class="widget">
          <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader"></div></div>
          <div class="widgetContent"><div class="dataTable" id="neighbour"></div></div>
        </div>
      </div>`;

    expect(findReportRoot(control('icon')).length).toBe(0);
  });

  // Same ambiguity without the loading window: a container widget pairing two reports under one
  // header gives its chrome controls no single report to act on.
  it('should return an empty set for a control shared by two reports', () => {
    document.body.innerHTML = `
      <div class="widget">
        <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div></div>
        <div class="widgetContent">
          <div class="dataTable" id="first"></div>
          <div class="dataTable" id="second"></div>
        </div>
      </div>`;

    expect(findReportRoot(control('icon')).length).toBe(0);
  });

  // Plugins mark their own report root with `data-report`, and it is not always a table:
  // CrashAnalytics puts it on a plain div rendered inside a popover. Requiring `.dataTable` here
  // turned their export icon into a no-op, without those plugins changing anything.
  it('should resolve a report a plugin marks on something other than a table', () => {
    document.body.innerHTML = `
      <div class="ui-dialog">
        <div class="crashLog" data-report="CrashAnalytics.getAllCrashes" id="report">
          <a id="icon"></a>
        </div>
      </div>`;

    expect(findReportRoot(control('icon'))[0]).toBe(document.querySelector('#report'));
  });

  it('should return an empty set when there is no report to find', () => {
    document.body.innerHTML = '<div class="pageWrap"><a id="icon"></a></div>';

    expect(findReportRoot(control('icon')).length).toBe(0);
  });
});
