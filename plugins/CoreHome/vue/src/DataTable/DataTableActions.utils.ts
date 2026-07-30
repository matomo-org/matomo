/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const { $ } = window;

/**
 * Resolves the datatable element a report action belongs to.
 *
 * The action row is rendered inside .dataTable, but on a full-page report it is moved out of it
 * and into the report header (see dataTable.js `adoptTableActionsIntoReportHeader`), so an
 * ancestor lookup no longer finds the table. The moved row carries the table's id instead.
 */
export function findActionDataTable(element: HTMLElement): JQuery {
  const $element = $(element);
  const $dataTable = $element.closest('[data-report]');

  if ($dataTable.length) {
    return $dataTable;
  }

  const dataTableId = $element.closest('[data-datatable-id]').attr('data-datatable-id');

  return dataTableId ? $(`#${dataTableId}`) : $();
}

export function isBooleanLikeSet(value: number|string|boolean): boolean {
  return !!value && value !== '0';
}

export function resolveExportSupportsFlat(
  reportSupportsFlatten: boolean,
  flatParam: number|string|boolean,
): boolean {
  return reportSupportsFlatten || isBooleanLikeSet(flatParam);
}
