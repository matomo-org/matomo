/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  computed,
  readonly,
} from 'vue';
import {
  AjaxHelper,
  NotificationsStore,
  translate,
} from 'CoreHome';

export interface ReportDeletionSettings {
  enableDeleteLogs?: boolean;
  enableDeleteReports?: boolean;
  deleteLogsOlderThan?: string;
  deleteReportsOlderThan?: string;
  keepBasic?: string;
  keepDay?: string;
  keepWeek?: string;
  keepMonth?: string;
  keepYear?: string;
  keepRange?: string;
  keepSegments?: string;
  deleteLowestInterval?: string;
}

interface ReportDeletionSettingsStoreState {
  settings: ReportDeletionSettings;
  showEstimate: boolean;
  loadingEstimation: boolean;
  estimation: string;
  isModified: boolean;
}

class ReportDeletionSettingsStore {
  private privateState = reactive<ReportDeletionSettingsStoreState>({
    settings: {},
    showEstimate: false,
    loadingEstimation: false,
    estimation: '',
    isModified: false,
  });

  readonly state = computed(() => readonly(this.privateState));

  readonly enableDeleteReports = computed(() => this.state.value.settings.enableDeleteReports);

  readonly enableDeleteLogs = computed(() => this.state.value.settings.enableDeleteLogs);

  private currentRequest?: AbortController;

  updateSettings(settings: ReportDeletionSettings) {
    this.initSettings(settings);
    this.privateState.isModified = true;
  }

  initSettings(settings: ReportDeletionSettings) {
    this.privateState.settings = { ...this.privateState.settings, ...settings };
    this.reloadDbStats();
  }

  savePurgeDataSettings(apiMethod: string, settings: ReportDeletionSettings, password: string) {
    this.privateState.isModified = false;
    return AjaxHelper.post(
      {
        module: 'API',
        method: apiMethod,
      },
      {
        ...settings,
        enableDeleteLogs: settings.enableDeleteLogs ? '1' : '0',
        enableDeleteReports: settings.enableDeleteReports ? '1' : '0',
        passwordConfirmation: password,
      },
    ).then(() => {
      const notificationInstanceId = NotificationsStore.show({
        message: translate('CoreAdminHome_SettingsSaveSuccess'),
        context: 'success',
        id: 'privacyManagerSettings',
        type: 'toast',
      });
      NotificationsStore.scrollToNotification(notificationInstanceId);
    });
  }

  isEitherDeleteSectionEnabled() {
    return this.state.value.settings.enableDeleteLogs
      || this.state.value.settings.enableDeleteReports;
  }

  isManualEstimationLinkShowing() {
    return window.$('#getPurgeEstimateLink').length > 0;
  }

  reloadDbStats(forceEstimate?: boolean) {
    if (this.currentRequest) { // if the manual estimate link is showing, abort unless forcing
      this.currentRequest.abort();
      this.currentRequest = undefined;
    }

    if (!forceEstimate
      && (!this.isEitherDeleteSectionEnabled()
        || this.isManualEstimationLinkShowing())
    ) {
      return;
    }

    this.privateState.loadingEstimation = true;
    this.privateState.estimation = '';
    this.privateState.showEstimate = false;

    const { settings } = this.privateState;
    const formData: QueryParameters = {
      ...settings,
      enableDeleteLogs: settings.enableDeleteLogs ? '1' : '0',
      enableDeleteReports: settings.enableDeleteReports ? '1' : '0',
    };

    if (forceEstimate === true) {
      formData.forceEstimate = 1;
    }

    this.currentRequest = new AbortController();
    AjaxHelper.post(
      {
        module: 'PrivacyManager',
        action: 'getDatabaseSize',
        format: 'html',
      },
      formData,
      { abortController: this.currentRequest, format: 'html' },
    ).then((data) => {
      this.privateState.estimation = data;
      this.privateState.showEstimate = true;
      this.privateState.loadingEstimation = false;
    }).finally(() => {
      this.currentRequest = undefined;
      this.privateState.loadingEstimation = false;
    });
  }
}

export default new ReportDeletionSettingsStore();
