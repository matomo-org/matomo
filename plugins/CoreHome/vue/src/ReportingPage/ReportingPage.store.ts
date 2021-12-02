/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import ReportingPagesStoreInstance, { Page } from '../ReportingPages/ReportingPages.store';
import ReportMetadataStoreInstance from '../ReportMetadata/ReportMetadata.store';
import { sortOrderables } from '../Orderable';
import { Widget } from '../Widget/Widgets.store';

interface ReportingMenuStoreState {
  page?: Page|null;
}

function shouldBeRenderedWithFullWidth(widget: Widget) {
  // rather controller logic
  if ((widget.isContainer && widget.layout && widget.layout === 'ByDimension')
    || widget.viewDataTable === 'bydimension'
  ) {
    return true;
  }

  if (widget.isWide) {
    return true;
  }

  return widget.viewDataTable
    && (widget.viewDataTable === 'tableAllColumns'
      || widget.viewDataTable === 'sparklines'
      || widget.viewDataTable === 'graphEvolution');
}

function markWidgetsInFirstRowOfPage(widgets: Widget[]) {
  if (widgets && widgets[0]) {
    const newWidgets = [...widgets];

    if (widgets[0].group) {
      newWidgets[0] = {
        ...newWidgets[0],
        left: markWidgetsInFirstRowOfPage(widgets[0].left),
        right: markWidgetsInFirstRowOfPage(widgets[0].right),
      };
    } else {
      newWidgets[0] = { ...newWidgets[0], isFirstInPage: true };
    }

    return newWidgets;
  }

  return widgets;
}

export class ReportingPageStore {
  private privateState = reactive<ReportingMenuStoreState>({
    page: null,
  });

  private state = computed(() => readonly(this.privateState));

  readonly page = computed(() => this.state.value.page);

  readonly widgets = computed(() => {
    const page = this.page.value;
    if (!page) {
      return [];
    }

    let widgets = [];
    const reportsToIgnore = {};

    const isIgnoredReport = (widget: Widget) => widget.isReport
      && reportsToIgnore[`${widget.module}.${widget.action}`];

    const getRelatedReports = (widget) => {
      if (!widget.isReport) {
        return [];
      }

      const report = ReportMetadataStoreInstance.findReport(widget.module, widget.action);
      if (!report || !report.relatedReports) {
        return [];
      }

      return report.relatedReports;
    };

    (page.widgets || []).forEach((widget) => {
      if (isIgnoredReport(widget)) {
        return;
      }

      getRelatedReports(widget).forEach((report) => {
        reportsToIgnore[`${report.module}.${report.action}`] = true;
      });

      widgets.push(widget);
    });

    widgets = sortOrderables(widgets);

    if (widgets.length === 1) {
      // if there is only one widget, we always display it full width
      return widgets;
    }

    let groupedWidgets = [];
    for (let i = 0; i < widgets.length; i += 1) {
      const widget = widgets[i];

      if (shouldBeRenderedWithFullWidth(widget)
        || (widgets[i + 1] && shouldBeRenderedWithFullWidth(widgets[i + 1]))
      ) {
        groupedWidgets.push({
          ...widget,
          widgets: sortOrderables(widget.widgets),
        });
      } else {
        let counter = 0;
        const left = [widget];
        const right = [];

        while (widgets[i + 1] && !shouldBeRenderedWithFullWidth(widgets[i + 1])) {
          i += 1;
          counter += 1;
          if (counter % 2 === 0) {
            left.push(widgets[i]);
          } else {
            right.push(widgets[i]);
          }
        }

        groupedWidgets.push({ group: true, left, right });
      }
    }

    groupedWidgets = markWidgetsInFirstRowOfPage(groupedWidgets);

    return groupedWidgets;
  });

  fetchPage(category: string, subcategory: string): Promise<typeof ReportingPageStore['page']['value']> {
    this.resetPage();

    return Promise.all([
      ReportingPagesStoreInstance.getAllPages(),
      ReportMetadataStoreInstance.fetchReportMetadata(),
    ]).then(() => {
      this.privateState.page = ReportingPagesStoreInstance.findPage(category, subcategory);
      return this.page.value;
    });
  }

  resetPage(): void {
    this.privateState.page = null;
  }
}

export default new ReportingPageStore();
