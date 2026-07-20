<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="dashboard-manager piwikSelector borderedControl piwikTopControl dashboardSettings"
    v-expand-on-click="{expander: 'expander', onExpand: onExpand, onClosed: onClosed}"
    @click="onOpen()"
    @focusout="onFocusOut"
  >
    <button
      type="button"
      class="title"
      v-tooltips
      :title="translate('Dashboard_ManageDashboard')"
      tabindex="4"
      ref="expander"
    >
      <span class="icon icon-dashboard-customize"></span>
      {{ translate('Dashboard_ManageDashboard') }}
    </button>
    <div
      class="dropdown positionInViewport"
      v-tooltips="{show: false}"
    >
      <ul class="submenu">
        <li
          v-for="(title, actionName) of generalActions"
          :key="actionName"
        >
          <button
            type="button"
            tabindex="4"
            @click="onClickAction($event, actionName)"
            class="generalAction"
            :disabled="isActionDisabled[actionName] ? 'disabled' : undefined"
            :title="actionTooltips[actionName] || undefined"
            :data-action="actionName"
          >
            {{ translate(title) }}
          </button>
        </li>
        <li>
          <button
            type="button"
            tabindex="4"
            class="exportDashboard"
            data-action="exportDashboard"
            @click="onClickExportDashboard()"
          >
            {{ translate('Dashboard_ExportThisDashboard') }}
          </button>
        </li>
        <li
          v-for="(title, actionName) of dashboardActions"
          :key="actionName"
        >
          <button
            type="button"
            tabindex="4"
            @click="onClickAction($event, actionName)"
            :disabled="isActionDisabled[actionName] ? 'disabled' : undefined"
            :title="actionTooltips[actionName] || undefined"
            :data-action="actionName"
          >
            {{ translate(title) }}
          </button>
        </li>
        <li class="addWidget">
          <button
            type="button"
            tabindex="4"
            class="addWidget-button"
            @click="openAddWidget()"
          >
            <span class="icon icon-add1"></span>{{ translate('Dashboard_AddAWidget') }}
          </button>
        </li>
      </ul>
    </div>
    <AddWidgetModal
      @select="onWidgetSelected"
    />
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
  MatomoUrl,
} from 'CoreHome';
import AddWidgetModal from '../AddWidgetModal/AddWidgetModal.vue';

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
const DASHBOARD_EXPORT_STORAGE_KEY = 'scheduledReports.dashboardExportId';

export default defineComponent({
  name: 'DashboardSettings',
  components: {
    AddWidgetModal,
  },
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
    const root = ref<HTMLElement|null>(null);

    onMounted(() => {
      Matomo.postEvent('Dashboard.DashboardSettings.mounted', root.value);
      $(root.value!).hide(); // hide dashboard-manager initially (shown manually by Dashboard.ts)
    });

    return {
      root,
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
    onExpand(event: MouseEvent|KeyboardEvent) {
      // Clicks triggered via keyboard (Enter/Space on the button) have detail === 0,
      // mouse clicks have detail >= 1. Only shift focus into the menu for keyboard opens.
      if ((event as MouseEvent).detail !== 0) {
        return;
      }
      this.$nextTick(() => {
        const firstAction = (this.$refs.root as HTMLElement)
          .querySelector<HTMLButtonElement>('.submenu button:not([disabled])');
        if (firstAction) {
          firstAction.focus();
        }
      });
    },
    onFocusOut(event: FocusEvent) {
      const root = this.$refs.root as HTMLElement;
      const newTarget = event.relatedTarget as Node | null;
      if (newTarget && root.contains(newTarget)) {
        return;
      }
      root.classList.remove('expanded');
    },
    onClosed(event: MouseEvent|KeyboardEvent) {
      // Return focus to the trigger when the dropdown was dismissed via the Escape
      // key, so keyboard users keep their place. Enter/Space activation of buttons
      // produces a MouseEvent (synthetic click) and is handled by the browser
      // leaving focus on the activated element.
      if (!(event instanceof KeyboardEvent)) {
        return;
      }
      const expander = this.$refs.expander as HTMLElement | undefined;
      if (expander) {
        expander.focus();
      }
    },
    openAddWidget() {
      // close the dashboard-manager dropdown when opening the modal
      (this.$refs.root as HTMLElement).classList.remove('expanded');
      Matomo.postEvent('Dashboard.AddWidget.open');
    },
    onWidgetSelected(widget: WidgetType) {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ($('#dashboardWidgetsArea') as any)
        .dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);
    },
    redirectToCreateScheduledReports() {
      const query = {
        ...MatomoUrl.urlParsed.value,
      } as QueryParameters;

      delete query.category;
      delete query.subcategory;
      delete query.idDashboard;
      query.module = 'ScheduledReports';
      query.action = 'index';

      const hash = {
        ...MatomoUrl.hashParsed.value,
      } as QueryParameters;

      delete hash.category;
      delete hash.subcategory;
      delete hash.idDashboard;
      MatomoUrl.updateUrl(query, hash);
    },

    redirectToLoginPage() {
      const loginQuery = {
        module: Matomo.getLoginModule(),
      } as QueryParameters;
      MatomoUrl.updateUrl(loginQuery);
    },

    onClickExportDashboard() {
      if (typeof sessionStorage !== 'undefined') {
        sessionStorage.removeItem(DASHBOARD_EXPORT_STORAGE_KEY);
      }

      if (this.isUserNotAnonymous) {
        const dashboardId = this.getCurrentDashboardId();
        if (dashboardId !== null && typeof sessionStorage !== 'undefined') {
          sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, String(dashboardId));
        }

        this.redirectToCreateScheduledReports();
        return;
      }
      // We do not persist dashboard id when user is anonymous
      this.redirectToLoginPage();
    },

    normalizeDashboardId(value: unknown): number|null {
      const candidate = Array.isArray(value) ? value[0] : value;
      if (candidate === null || candidate === undefined) {
        return null;
      }

      const normalized = String(candidate).trim();
      if (!/^[1-9]\d*$/.test(normalized)) {
        return null;
      }

      return Number(normalized);
    },
    getCurrentDashboardId(): number|null {
      const fromSubcategory = this.normalizeDashboardId(MatomoUrl.getSearchParam('subcategory'));
      if (fromSubcategory !== null) {
        return fromSubcategory;
      }

      const fromQueryIdDashboard = this.normalizeDashboardId(MatomoUrl.urlParsed.value.idDashboard);
      if (fromQueryIdDashboard !== null) {
        return fromQueryIdDashboard;
      }

      return this.normalizeDashboardId(MatomoUrl.hashParsed.value.idDashboard);
    },
  },
});
</script>
