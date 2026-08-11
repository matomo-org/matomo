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

  it('should return an empty set when there is no report to find', () => {
    document.body.innerHTML = '<div class="pageWrap"><a id="icon"></a></div>';

    expect(findReportRoot(control('icon')).length).toBe(0);
  });
});
