/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const { $ } = window;

const HEADER = '[vue-entry="CoreHome.ReportHeader"]';

// Drops the tables nested inside another table in the same set, i.e. expanded subtables, leaving
// one entry per report.
function outermost(tables: JQuery): JQuery {
  return tables.not(tables.find('.dataTable'));
}

// A report owns exactly one table. Anything else means the control cannot be attributed to a single
// report, and guessing would silently act on the wrong one.
function onlyReport(tables: JQuery): JQuery {
  const reports = outermost(tables);
  return reports.length === 1 ? reports : $();
}

/**
 * Returns the `.dataTable` of the report an action control belongs to, or an empty set.
 *
 * The controls used to live only inside the table, so `closest('.dataTable')` was enough. They are
 * moving up into the shared ReportHeader, which twig renders *outside* the table so an ajax reload
 * cannot replace it - from there `closest()` walks past the report entirely.
 *
 * Resolving upwards to the nearest ancestor that merely *holds* a table is not enough either: a
 * dashboard widget renders its header before its table, so between the two there is a window where
 * the control exists and its own report does not, and climbing past it lands on the neighbouring
 * widget's table. So anchor on the header the control sits in and take the report beside it, which
 * is the adjacency `dataTable.js:_locateReportHeader()` resolves for the jQuery-side handlers.
 *
 * Callers must handle the empty set: it means this control has no one report to act on, not that
 * the page has none.
 */
export default function findReportRoot(el: HTMLElement): JQuery {
  const $el = $(el);

  const enclosing = $el.closest('.dataTable');
  if (enclosing.length) {
    return enclosing;
  }

  const $header = $el.closest(HEADER);
  if (!$header.length) {
    return $();
  }

  // Full-page report: the table follows the header inside their shared wrapper, either as its next
  // sibling or, for an empty titled report, wrapped in `.card > .card-content` (_dataTable.twig).
  const $following = $header.nextAll();
  const beside = $following.filter('.dataTable').add($following.find('.dataTable'));
  if (beside.length) {
    return onlyReport(beside);
  }

  // Widget: the header lives in the widget chrome, above the content holding the table.
  const $widget = $header.closest('.widget');
  if ($widget.length) {
    return onlyReport($widget.find('.dataTable'));
  }

  return $();
}
