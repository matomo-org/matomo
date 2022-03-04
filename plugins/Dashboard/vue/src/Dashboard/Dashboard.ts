/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding, watch } from 'vue';
import { Matomo, MatomoUrl } from 'CoreHome';
import DashboardStore from './Dashboard.store';
import { Dashboard, DashboardLayout } from '../types';

interface DashboardDirectiveArgs {
  idDashboard: string|number;
  layout?: unknown;
}

const { $ } = window;

function renderDashboard(
  dashboardId: string|number,
  dashboard: Dashboard,
  layout: DashboardLayout,
) {
  const $settings = $('.dashboardSettings');

  $settings.show();
  window.initTopControls();

  // Embed dashboard / exported as widget
  if (!$('#topBars').length) {
    $settings.after($('#Dashboard'));
    $('#Dashboard ul li').removeClass('active');
    $(`#Dashboard_embeddedIndex_${dashboardId}`).addClass('active');
  }

  window.widgetsHelper.getAvailableWidgets();

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  ($('#dashboardWidgetsArea') as any)
    .off('dashboardempty', window.showEmptyDashboardNotification)
    .on('dashboardempty', window.showEmptyDashboardNotification)
    .dashboard({
      idDashboard: dashboardId,
      layout,
      name: dashboard ? dashboard.name : '',
    });

  const divElements = $('#columnPreview').find('>div');
  divElements.each(function eachPreview() {
    const width: string[] = [];
    $('div', this).each(function eachDiv() {
      width.push(this.className.replace(/width-/, ''));
    });
    $(this).attr('layout', width.join('-'));
  });

  divElements.off('click.renderDashboard');
  divElements.on('click.renderDashboard', function onRenderDashboard() {
    divElements.removeClass('choosen');
    $(this).addClass('choosen');
  });
}

function fetchDashboard(dashboardId: string|number) {
  window.globalAjaxQueue.abort();

  return new Promise((resolve) => setTimeout(resolve)).then(
    () => Promise.resolve(window.widgetsHelper.firstGetAvailableWidgetsCall),
  ).then(() => {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const dashboardElement = $('#dashboardWidgetsArea') as any;
    dashboardElement.dashboard('destroyWidgets');
    dashboardElement.empty();

    return Promise.all([
      DashboardStore.getDashboard(dashboardId),
      DashboardStore.getDashboardLayout(dashboardId),
    ]);
  }).then(([dashboard, layout]) => new Promise<void>((resolve) => {
    $(() => {
      renderDashboard(dashboardId, dashboard as Dashboard, layout as DashboardLayout);
      resolve();
    });
  }));
}

function clearDashboard() {
  $('.top_controls .dashboard-manager').hide();
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  ($('#dashboardWidgetsArea') as any).dashboard('destroy');
}

function onLocationChange(parsed: (typeof MatomoUrl)['urlParsed']['value']) {
  if (parsed.module !== 'Widgetize' && parsed.category !== 'Dashboard_Dashboard') {
    // we remove the dashboard only if we no longer show a dashboard.
    clearDashboard();
  }
}
interface LoadPageArgs {
  category: string;
  subcategory: string;
  promise?: Promise<void>;
}

function onLoadPage(params: LoadPageArgs) {
  if (params.category === 'Dashboard_Dashboard'
    && $.isNumeric(params.subcategory)
  ) {
    params.promise = fetchDashboard(parseInt(params.subcategory, 10));
  }
}

function onLoadDashboard(idDashboard: string|number) {
  fetchDashboard(idDashboard);
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<DashboardDirectiveArgs>): void {
    fetchDashboard(binding.value.idDashboard);

    watch(() => MatomoUrl.parsed.value, (parsed) => {
      onLocationChange(parsed);
    });

    // load dashboard directly since it will be faster than going through reporting page API
    Matomo.on('ReportingPage.loadPage', onLoadPage);

    Matomo.on('Dashboard.loadDashboard', onLoadDashboard);
  },
  unmounted(): void {
    onLocationChange(MatomoUrl.parsed.value);

    Matomo.off('ReportingPage.loadPage', onLoadPage);
    Matomo.off('Dashboard.loadDashboard', onLoadDashboard);
  },
};
