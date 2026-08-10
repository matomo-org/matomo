/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const { $ } = window;

/**
 * Returns the `.dataTable` of the report an action control belongs to.
 *
 * The controls used to live only inside the table, so `closest('.dataTable')` was enough. They are
 * moving up into the shared ReportHeader, which twig renders *outside* the table so an ajax reload
 * cannot replace it - from there `closest()` walks past the report entirely.
 *
 * So: use the enclosing table when there is one, otherwise the nearest ancestor that holds a table.
 * That ancestor is the per-report wrapper (`.card-content`, the bare `div` of a titled report, or
 * `.widget`), which is the same adjacency `dataTable.js:_findReportScope()` relies on. Taking the
 * *nearest* such ancestor is what keeps a page of sibling reports from resolving to each other.
 */
export default function findReportRoot(el: HTMLElement): JQuery {
  const $el = $(el);

  const enclosing = $el.closest('.dataTable');
  if (enclosing.length) {
    return enclosing;
  }

  return $el.parents()
    .filter((index: number, node: HTMLElement) => $(node).find('.dataTable').length > 0)
    .first()
    .find('.dataTable')
    .first();
}
