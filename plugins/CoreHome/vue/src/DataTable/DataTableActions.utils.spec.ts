/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  findActionDataTable,
  isBooleanLikeSet,
  resolveExportSupportsFlat,
} from './DataTableActions.utils';

describe('CoreHome/DataTableActions.utils', () => {
  describe('findActionDataTable', () => {
    afterEach(() => {
      document.body.innerHTML = '';
    });

    it('finds the datatable an action still sits inside', () => {
      document.body.innerHTML = `
        <div id="dataTable_1" class="dataTable" data-report="Actions.getPageUrls">
          <div class="dataTableHeaderControls">
            <a class="dataTableAction" id="theAction"></a>
          </div>
        </div>`;

      const found = findActionDataTable(document.getElementById('theAction')!);

      expect(found.length).toBe(1);
      expect(found.attr('id')).toBe('dataTable_1');
    });

    it('finds it via the id stamp once the action row was moved into the report header', () => {
      // the header is a sibling of .dataTable, so an ancestor lookup cannot reach the table
      document.body.innerHTML = `
        <div class="reportHeader">
          <div class="reportHeader__actions">
            <div class="dataTableHeaderControls" data-datatable-id="dataTable_1">
              <a class="dataTableAction" id="theAction"></a>
            </div>
          </div>
        </div>
        <div id="dataTable_1" class="dataTable" data-report="Actions.getPageUrls"></div>`;

      const found = findActionDataTable(document.getElementById('theAction')!);

      expect(found.length).toBe(1);
      expect(found.attr('id')).toBe('dataTable_1');
    });

    it('returns an empty set when neither the table nor a stamp is reachable', () => {
      document.body.innerHTML = '<a class="dataTableAction" id="theAction"></a>';

      expect(findActionDataTable(document.getElementById('theAction')!).length).toBe(0);
    });
  });

  describe('isBooleanLikeSet', () => {
    it.each([
      [true, true],
      [1, true],
      ['1', true],
      [false, false],
      [0, false],
      ['0', false],
    ])('returns %s => %s', (value: number|string|boolean, expected: boolean) => {
      expect(isBooleanLikeSet(value)).toBe(expected);
    });
  });

  describe('resolveExportSupportsFlat', () => {
    it('keeps flat export available when the report supports flattening', () => {
      expect(resolveExportSupportsFlat(true, 0)).toBe(true);
    });

    it('keeps flat export available when the table is already flat', () => {
      expect(resolveExportSupportsFlat(false, 1)).toBe(true);
    });

    it('disables flat export when neither report nor table state supports it', () => {
      expect(resolveExportSupportsFlat(false, 0)).toBe(false);
    });
  });
});
