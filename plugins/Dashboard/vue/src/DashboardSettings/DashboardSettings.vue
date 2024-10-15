<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="dashboard-manager piwikSelector borderedControl piwikTopControl dashboardSettings"
    v-expand-on-click="{expander: 'expander', onClosed: onClose}"
    v-tooltips="{show: false}"
    @click="onOpen()"
  >
    <a
      class="title"
      :title="translate('Dashboard_ManageDashboard')"
      tabindex="4"
      ref="expander"
    >
      <span class="icon icon-chevron-down"></span>{{ translate('Dashboard_Dashboard') }}
    </a>
    <div class="dropdown positionInViewport">
      <ul class="submenu">
        <li>
          <div class="addWidget">{{ translate('Dashboard_AddAWidget') }}</div>
          <ul class="widgetpreview-categorylist"></ul>
        </li>
        <li>
          <div class="manageDashboard">{{ translate('Dashboard_ManageDashboard') }}</div>

          <ul>
            <li
              v-for="(title, actionName) of dashboardActions"
              :key="actionName"
              @click="onClickAction($event, actionName)"
              :disabled="isActionDisabled[actionName] ? 'disabled' : undefined"
              :title="actionTooltips[actionName] || undefined"
              :data-action="actionName"
            >
              {{ translate(title) }}
            </li>
          </ul>
        </li>
        <li
          v-for="(title, actionName) of generalActions"
          :key="actionName"
          @click="onClickAction($event, actionName)"
          class="generalAction"
          :disabled="isActionDisabled[actionName] ? 'disabled' : undefined"
          :title="actionTooltips[actionName] || undefined"
          :data-action="actionName"
        >
          {{ translate(title) }}
        </li>
      </ul>
      <ul class="widgetpreview-widgetlist"></ul>
      <div class="widgetpreview-preview"></div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue';
import {
  Matomo,
  ExpandOnClick,
  Tooltips,
  translate,
  WidgetType,
} from 'CoreHome';

declare global {
  interface Window {
    resetDashboard(): void;
    showChangeDashboardLayoutDialog(): void;
    renameDashboard(): void;
    removeDashboard(): void;
    setAsDefaultWidgets(): void;
    copyDashboardToUser(): void;
    createDashboard(): void;
  }
}

interface DashboardSettingsState {
  isActionDisabled: Record<keyof Window, boolean>;
  actionTooltips: Record<keyof Window, string|undefined>;
}

const { $ } = window;

function isWidgetAvailable(widgetUniqueId: string) {
  return !$('#dashboardWidgetsArea').find(`[widgetId="${widgetUniqueId}"]`).length;
}

function widgetSelected(widget: WidgetType) {
  // for UI tests (see DashboardManager_spec.js)
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  if ((window as any).MATOMO_DASHBOARD_SETTINGS_WIDGET_SELECTED_NOOP) {
    return;
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  ($('#dashboardWidgetsArea') as any)
    .dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);
}

export default defineComponent({
  directives: {
    ExpandOnClick,
    Tooltips,
  },
  data(): DashboardSettingsState {
    return {
      isActionDisabled: {} as Record<keyof Window, boolean>,
      actionTooltips: {} as Record<keyof Window, string>,
    };
  },
  setup() {
    // $.widgetMenu will modify the jquery object it's given, so we have to save it and reuse
    // it to call functions.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const rootJQuery = ref<any>(null);

    const root = ref<HTMLElement|null>(null);

    const createWidgetPreview = () => {
      rootJQuery.value.widgetPreview({
        isWidgetAvailable,
        onSelect: (widgetUniqueId: string) => {
          window.widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, (widget) => {
            (root.value as HTMLElement).click(); // close selector

            widgetSelected(widget as WidgetType);
          });
        },
        resetOnSelect: true,
      });
    };

    onMounted(() => {
      Matomo.postEvent('Dashboard.DashboardSettings.mounted', root.value);

      rootJQuery.value = $(root.value!);
      createWidgetPreview();

      // When the available widgets list is reloaded, re-create the widget preview to include update
      Matomo.on('WidgetsStore.reloaded', () => {
        createWidgetPreview();
      });

      rootJQuery.value.hide(); // hide dashboard-manager initially (shown manually by Dashboard.ts)
    });

    return {
      root,
      rootJQuery,
    };
  },
  computed: {
    isUserNotAnonymous(): boolean {
      return !!Matomo.userLogin && Matomo.userLogin !== 'anonymous';
    },
    isSuperUser(): boolean {
      return this.isUserNotAnonymous && Matomo.hasSuperUserAccess;
    },
    isUserHasSomeAdminAccess(): boolean {
      return this.isUserNotAnonymous && Matomo.userHasSomeAdminAccess;
    },
    dashboardActions(): Record<keyof Window, string> {
      const result = {
        resetDashboard: 'Dashboard_ResetDashboard',
        showChangeDashboardLayoutDialog: 'Dashboard_ChangeDashboardLayout',
      } as Record<keyof Window, string>;

      if (this.isUserNotAnonymous) {
        result.renameDashboard = 'Dashboard_RenameDashboard';
        result.removeDashboard = 'Dashboard_RemoveDashboard';
      }

      if (this.isSuperUser) {
        result.setAsDefaultWidgets = 'Dashboard_SetAsDefaultWidgets';
      }

      if (this.isUserHasSomeAdminAccess) {
        result.copyDashboardToUser = 'Dashboard_CopyDashboardToUser';
      }

      return result;
    },
    generalActions(): Record<keyof Window, string> {
      const result = {} as Record<keyof Window, string>;

      if (this.isUserNotAnonymous) {
        result.createDashboard = 'Dashboard_CreateNewDashboard';
      }

      return result;
    },
  },
  methods: {
    onClickAction(event: Event, action: keyof Window) {
      if ((event.target as HTMLElement).getAttribute('disabled')) {
        return;
      }

      (window[action] as (() => void))();
    },
    onOpen() {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      if (($('#dashboardWidgetsArea') as any).dashboard('isDefaultDashboard')) {
        this.isActionDisabled.removeDashboard = true;
        this.actionTooltips.removeDashboard = translate('Dashboard_RemoveDefaultDashboardNotPossible');
      } else {
        this.isActionDisabled.removeDashboard = false;
        this.actionTooltips.removeDashboard = undefined;
      }
    },
    onClose() {
      this.rootJQuery.widgetPreview('reset');
    },
  },
});
</script>
