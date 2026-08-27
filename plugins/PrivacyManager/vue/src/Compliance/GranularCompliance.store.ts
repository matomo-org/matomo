/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  computed,
  ComputedRef,
  DeepReadonly,
  reactive,
  readonly,
} from 'vue';
import { AjaxHelper } from 'CoreHome';

export interface PolicySetting {
  id: string;
  name: string;
  whatItDoes: string;
  impact: string;
  status: string;
  enforced: boolean | null;
  toggleable: boolean;
  section: string;
}

export interface PolicySettingsPayload {
  policy: string;
  title: string;
  description: string;
  configControlled: boolean;
  policyEnforced: boolean;
  settings: PolicySetting[];
}

interface GranularComplianceStoreState {
  idSite: string | null;
  loading: boolean;
  saving: boolean;
  description: string;
  configControlled: boolean;
  policyEnforced: boolean;
  settings: PolicySetting[];
  localEnforced: Record<string, boolean>;
  fetchError: string | null;
  saveError: string | null;
  saveSuccess: boolean;
}

export interface GranularComplianceStore {
  state: DeepReadonly<GranularComplianceStoreState>;
  dirtySettingIds: ComputedRef<string[]>;
  setIdSite: (idSite: string) => void;
  toggleSetting: (settingId: string) => void;
  enforceAll: () => void;
  save: (password: string) => void;
}

export function createGranularComplianceStore(policy: string): GranularComplianceStore {
  const state = reactive<GranularComplianceStoreState>({
    idSite: null,
    loading: false,
    saving: false,
    description: '',
    configControlled: false,
    policyEnforced: false,
    settings: [],
    localEnforced: {},
    fetchError: null,
    saveError: null,
    saveSuccess: false,
  });

  function applyPayload(payload: PolicySettingsPayload) {
    state.description = payload.description;
    state.configControlled = payload.configControlled;
    state.policyEnforced = payload.policyEnforced;
    state.settings = payload.settings;

    const localEnforced: Record<string, boolean> = {};
    payload.settings.forEach((setting) => {
      if (setting.toggleable) {
        localEnforced[setting.id] = !!setting.enforced;
      }
    });
    state.localEnforced = localEnforced;
  }

  const dirtySettingIds = computed(
    () => state.settings
      .filter((s) => s.toggleable && state.localEnforced[s.id] !== !!s.enforced)
      .map((s) => s.id),
  );

  function fetchSettings() {
    if (!state.idSite) {
      return;
    }

    // guard against out-of-order responses when the site changes mid-request
    const requestedIdSite = state.idSite;

    state.loading = true;
    state.fetchError = null;
    AjaxHelper.fetch<PolicySettingsPayload>(
      {
        idSite: requestedIdSite,
        compliancePolicy: policy,
        method: 'PrivacyManager.getCompliancePolicySettings',
      },
      {
        createErrorNotification: false,
      },
    ).then((payload) => {
      if (state.idSite === requestedIdSite) {
        applyPayload(payload);
      }
    }).catch((error) => {
      if (state.idSite === requestedIdSite) {
        state.fetchError = error.message || error;
      }
    }).finally(() => {
      if (state.idSite === requestedIdSite) {
        state.loading = false;
      }
    });
  }

  function setIdSite(idSite: string) {
    state.idSite = idSite;
    state.saveError = null;
    state.saveSuccess = false;
    fetchSettings();
  }

  function toggleSetting(settingId: string) {
    if (state.configControlled || !(settingId in state.localEnforced)) {
      return;
    }

    state.localEnforced[settingId] = !state.localEnforced[settingId];
    state.saveSuccess = false;
  }

  function enforceAll() {
    if (state.configControlled) {
      return;
    }

    state.settings.forEach((setting) => {
      if (setting.toggleable) {
        state.localEnforced[setting.id] = true;
      }
    });
  }

  function save(password: string) {
    const settingValues: Record<string, number> = {};
    dirtySettingIds.value.forEach((settingId) => {
      settingValues[settingId] = state.localEnforced[settingId] ? 1 : 0;
    });

    if (!Object.keys(settingValues).length) {
      return;
    }

    // guard against applying a save response after the site changed mid-request
    const requestedIdSite = state.idSite;

    state.saving = true;
    state.saveError = null;
    state.saveSuccess = false;
    AjaxHelper.post<PolicySettingsPayload>(
      {
        idSite: requestedIdSite,
        compliancePolicy: policy,
        method: 'PrivacyManager.setCompliancePolicySettings',
      },
      {
        settingValues,
        passwordConfirmation: password,
      },
      {
        createErrorNotification: false,
      },
    ).then((payload) => {
      if (state.idSite === requestedIdSite) {
        applyPayload(payload);
        state.saveSuccess = true;
      }
    }).catch((error) => {
      if (state.idSite === requestedIdSite) {
        state.saveError = error.message || error;
      }
    }).finally(() => {
      state.saving = false;
    });
  }

  const publicState = readonly(state) as DeepReadonly<GranularComplianceStoreState>;

  return {
    state: publicState,
    dirtySettingIds,
    setIdSite,
    toggleSetting,
    enforceAll,
    save,
  };
}
