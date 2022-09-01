<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    :class="{ widgetBody: isWidget }"
    id="transitions_report"
  >
    <div class="row">
      <div class="col s12 m3">
        <div name="actionType">
          <Field
            uicontrol="select"
            name="actionType"
            v-model="actionType"
            :title="translate('Actions_ActionType')"
            :full-width="true"
            :options="actionTypeOptions"
          >
          </Field>
        </div>
      </div>
      <div class="col s12 m9">
        <div name="actionName">
          <Field
            uicontrol="select"
            name="actionName"
            v-model="actionName"
            :title="translate('Transitions_TopX', 100)"
            :full-width="true"
            :disabled="!isEnabled"
            :options="actionNameOptions"
          >
          </Field>
        </div>
      </div>
    </div>
    <ActivityIndicator :loading="isLoading" />
    <div
      class="loadingPiwik"
      style="display:none;"
      id="transitions_inline_loading"
    >
      <img src="plugins/Morpheus/images/loading-blue.gif" alt/>
      <span>{{ translate('General_LoadingData') }}</span>
    </div>
    <div
      class="popoverContainer"
      v-show="!isLoading && isEnabled"
    >
    </div>
    <div
      id="Transitions_Error_Container"
      v-show="!isLoading"
    >
    </div>
    <div
      class="dataTableWrapper"
      v-show="isEnabled"
    >
      <div class="dataTableFeatures">
        <div class="dataTableFooterNavigation">
          <div class="dataTableControls">
            <div class="row">
              <a
                class="dataTableAction"
                v-transition-exporter
              >
                <span class="icon-export" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="alert alert-info">
      {{ translate('Transitions_AvailableInOtherReports') }}
      {{ translate('Actions_PageUrls') }}, {{ translate('Actions_SubmenuPageTitles') }},
      {{ translate('Actions_SubmenuPagesEntry') }}
      {{ translate('General_And') }}
      {{ translate('Actions_SubmenuPagesExit') }}.
      <span v-html="$sanitize(availableInOtherReports2)"></span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onBeforeUnmount, ref } from 'vue';
import {
  translate,
  AjaxHelper,
  Matomo,
  ActivityIndicator,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import TransitionExporter from '../TransitionExporter/TransitionExporter';

interface Option {
  key: string;
  value: unknown;
  url?: string;
}

interface TransitionSwitcherState {
  actionType: string;
  actionNameOptions: Option[];
  actionTypeOptions: Option[];
  isLoading: boolean;
  actionName: string|null;
  isEnabled: boolean;
  noDataKey: string;
}

interface ActionReportRow {
  label: string;
  nb_hits: string|number;
  segment: string;
  url: string;
}

export default defineComponent({
  props: {
    isWidget: Boolean,
  },
  components: {
    Field,
    ActivityIndicator,
  },
  directives: {
    TransitionExporter,
  },
  data(): TransitionSwitcherState {
    return {
      actionType: 'Actions.getPageUrls',
      actionNameOptions: [],
      actionTypeOptions: [
        {
          key: 'Actions.getPageUrls',
          value: translate('Actions_PageUrls'),
        }, {
          key: 'Actions.getPageTitles',
          value: translate('Actions_WidgetPageTitles'),
        },
      ],
      isLoading: false,
      actionName: null,
      isEnabled: true,
      noDataKey: '_____ignore_____',
    };
  },
  setup() {
    let transitionsInstance: Transitions|null = null;
    const transitionsUrl = ref<null|string>();

    const onSwitchTransitionsUrl = (params: { url: string }) => {
      if (params?.url) {
        transitionsUrl.value = params.url;
      }
    };

    Matomo.on('Transitions.switchTransitionsUrl', onSwitchTransitionsUrl);

    onBeforeUnmount(() => {
      Matomo.off('Transitions.switchTransitionsUrl', onSwitchTransitionsUrl);
    });

    const createTransitionsInstance = (type: string, actionName: string) => {
      if (!transitionsInstance) {
        transitionsInstance = new window.Piwik_Transitions(type, actionName, null, '');
      } else {
        transitionsInstance.reset(type, actionName, '');
      }
    };

    const getTransitionsInstance = () => transitionsInstance;

    return {
      transitionsUrl,
      createTransitionsInstance,
      getTransitionsInstance,
    };
  },
  watch: {
    transitionsUrl(newValue) {
      let url = newValue;
      if (this.isUrlReport) {
        url = url.replace('https://', '').replace('http://', '');
      }

      const found = this.actionNameOptions.find((option) => {
        let optionUrl = option.url;

        if (optionUrl && this.isUrlReport) {
          optionUrl = String(optionUrl).replace('https://', '').replace('http://', '');
        } else {
          optionUrl = undefined;
        }

        return option.key === url || (url === optionUrl && optionUrl);
      });

      if (found) {
        this.actionName = found.key;
      } else {
        // we only fetch top 100 in the report... so the entry the user clicked on, might not
        // be in the top 100
        this.actionNameOptions = [
          ...this.actionNameOptions,
          { key: url, value: url },
        ];
        this.actionName = url;
      }
    },
    actionName(newValue) {
      if (newValue === null || newValue === this.noDataKey) {
        return;
      }

      const type = this.isUrlReport ? 'url' : 'title';

      this.createTransitionsInstance(type, newValue);

      this.getTransitionsInstance()!.showPopover(true);
    },
    actionType(newValue) {
      this.fetch(newValue);
    },
  },
  created() {
    this.fetch(this.actionType);
  },
  methods: {
    detectActionName(reports: ActionReportRow[]) {
      const othersLabel = translate('General_Others');

      reports.forEach((report) => {
        if (!report) {
          return;
        }

        if (report.label === othersLabel) {
          return;
        }

        const key = this.isUrlReport ? report.url : report.label;
        if (key) {
          const pageviews = translate('Transitions_NumPageviews', report.nb_hits as string);
          const label = `${report.label} (${pageviews})`;
          this.actionNameOptions.push({
            key,
            value: label,
            url: report.url,
          });

          if (!this.actionName) {
            this.actionName = key;
          }
        }
      });
    },
    fetch(type: string) {
      this.isLoading = true;
      this.actionNameOptions = [];
      this.actionName = null;
      AjaxHelper.fetch<ActionReportRow[]>({
        method: type,
        flat: 1,
        filter_limit: 100,
        filter_sort_order: 'desc',
        filter_sort_column: 'nb_hits',
        showColumns: 'label,nb_hits,url',
      }).then((report) => {
        this.isLoading = false;
        this.actionNameOptions = [];
        this.actionName = null;

        if (report?.length) {
          this.isEnabled = true;
          this.detectActionName(report);
        }

        if (this.actionName === null || this.actionNameOptions.length === 0) {
          this.isEnabled = false;
          this.actionName = this.noDataKey;
          this.actionNameOptions.push({
            key: this.noDataKey,
            value: translate('CoreHome_ThereIsNoDataForThisReport'),
          });
        }
      }).catch(() => {
        this.isLoading = false;
        this.isEnabled = false;
      });
    },
  },
  computed: {
    isUrlReport() {
      return this.actionType === 'Actions.getPageUrls';
    },
    availableInOtherReports2() {
      return translate('Transitions_AvailableInOtherReports2', '<span class="icon-transition"></span>');
    },
  },
});
</script>
