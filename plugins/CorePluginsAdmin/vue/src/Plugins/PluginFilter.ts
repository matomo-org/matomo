/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ClickEvent = JQuery.ClickEvent;

const { $ } = window;

function getCurrentFilterOrigin(element: JQuery) {
  return element.find('.origin a.active').data('filter-origin');
}

function getCurrentFilterStatus(element: JQuery) {
  return element.find('.status a.active').data('filter-status');
}

function getMatchingNodes(filterOrigin: string, filterStatus: string): JQuery {
  let query = '#plugins tr';

  if (filterOrigin === 'all') {
    query += '[data-filter-origin]';
  } else {
    query += `[data-filter-origin=${filterOrigin}]`;
  }

  if (filterStatus === 'all') {
    query += '[data-filter-status]';
  } else {
    query += `[data-filter-status=${filterStatus}]`;
  }

  return $(query);
}

function updateNumberOfMatchingPluginsInFilter(
  element: JQuery,
  selectorFilterToUpdate: string,
  filterOrigin: string,
  filterStatus: string,
) {
  const numMatchingNodes = getMatchingNodes(filterOrigin, filterStatus).length;
  const updatedCounterText = ` (${numMatchingNodes})`;

  element.find(`${selectorFilterToUpdate} .counter`).text(updatedCounterText);
}

function updateAllNumbersOfMatchingPluginsInFilter(element: JQuery) {
  const filterOrigin = getCurrentFilterOrigin(element);
  const filterStatus = getCurrentFilterStatus(element);

  updateNumberOfMatchingPluginsInFilter(
    element,
    '[data-filter-status="all"]',
    filterOrigin,
    'all',
  );
  updateNumberOfMatchingPluginsInFilter(
    element,
    '[data-filter-status="active"]',
    filterOrigin,
    'active',
  );
  updateNumberOfMatchingPluginsInFilter(
    element,
    '[data-filter-status="inactive"]',
    filterOrigin,
    'inactive',
  );

  updateNumberOfMatchingPluginsInFilter(
    element,
    '[data-filter-origin="all"]',
    'all',
    filterStatus,
  );
  updateNumberOfMatchingPluginsInFilter(
    element,
    '[data-filter-origin="core"]',
    'core',
    filterStatus,
  );
  updateNumberOfMatchingPluginsInFilter(
    element,
    '[data-filter-origin="official"]',
    'official',
    filterStatus,
  );
  updateNumberOfMatchingPluginsInFilter(
    element,
    '[data-filter-origin="thirdparty"]',
    'thirdparty',
    filterStatus,
  );
}

function filterPlugins(element: JQuery) {
  const filterOrigin = getCurrentFilterOrigin(element);
  const filterStatus = getCurrentFilterStatus(element);

  const $nodesToEnable = getMatchingNodes(filterOrigin, filterStatus);

  $('#plugins tr[data-filter-origin][data-filter-status]').css('display', 'none');
  $nodesToEnable.css('display', 'table-row');

  updateAllNumbersOfMatchingPluginsInFilter(element);
}

function onClickStatus(element: JQuery, event: ClickEvent) {
  event.preventDefault();

  $(event.target as HTMLElement).siblings().removeClass('active');
  $(event.target as HTMLElement).addClass('active');

  filterPlugins(element);
}

function onClickOrigin(element: JQuery, event: ClickEvent) {
  event.preventDefault();

  $(event.target as HTMLElement).siblings().removeClass('active');
  $(event.target as HTMLElement).addClass('active');

  filterPlugins(element);
}

export default {
  mounted(el: HTMLElement): void {
    setTimeout(() => {
      updateAllNumbersOfMatchingPluginsInFilter($(el));

      $(el).find('.status').on('click', 'a', onClickStatus.bind(null, $(el)));
      $(el).find('.origin').on('click', 'a', onClickOrigin.bind(null, $(el)));
    });
  },
};
