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

export interface HybridTrackingPayload {
  enabled: boolean;
  enabledForAllWebsites: boolean;
  isWritable: boolean;
}

interface HybridTrackingStoreState {
  idSite: string | null;
  loading: boolean;
  saving: boolean;
  enabled: boolean;
  enabledForAllWebsites: boolean;
  isWritable: boolean;
  localEnabled: boolean;
  fetchError: string | null;
  saveError: string | null;
  saveSuccess: boolean;
}

export interface HybridTrackingStore {
  state: DeepReadonly<HybridTrackingStoreState>;
  hasUnsavedChanges: ComputedRef<boolean>;
  setIdSite: (idSite: string) => void;
  toggle: () => void;
  save: (password: string) => void;
}

export function createHybridTrackingStore(): HybridTrackingStore {
  const state = reactive<HybridTrackingStoreState>({
    idSite: null,
    loading: false,
    saving: false,
    enabled: false,
    enabledForAllWebsites: false,
    isWritable: false,
    localEnabled: false,
    fetchError: null,
    saveError: null,
    saveSuccess: false,
  });

  function applyPayload(payload: HybridTrackingPayload) {
    state.enabled = payload.enabled;
    state.enabledForAllWebsites = payload.enabledForAllWebsites;
    state.isWritable = payload.isWritable;
    state.localEnabled = payload.enabled;
  }

  const hasUnsavedChanges = computed(() => state.localEnabled !== state.enabled);

  function fetch() {
    if (!state.idSite) {
      return;
    }

    // guard against out-of-order responses when the site changes mid-request
    const requestedIdSite = state.idSite;

    state.loading = true;
    state.fetchError = null;
    AjaxHelper.fetch<HybridTrackingPayload>(
      {
        idSite: requestedIdSite,
        method: 'PrivacyManager.getConsentAwareAnalytics',
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
    fetch();
  }

  function toggle() {
    if (!state.isWritable) {
      return;
    }

    state.localEnabled = !state.localEnabled;
    state.saveSuccess = false;
  }

  function save(password: string) {
    if (!hasUnsavedChanges.value) {
      return;
    }

    // guard against applying a save response after the site changed mid-request
    const requestedIdSite = state.idSite;

    state.saving = true;
    state.saveError = null;
    state.saveSuccess = false;
    AjaxHelper.post<HybridTrackingPayload>(
      {
        idSite: requestedIdSite,
        method: 'PrivacyManager.setConsentAwareAnalytics',
      },
      {
        // a boolean would be posted as the string "false", which PHP reads as true
        enabled: state.localEnabled ? 1 : 0,
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

  return {
    state: readonly(state) as DeepReadonly<HybridTrackingStoreState>,
    hasUnsavedChanges,
    setIdSite,
    toggle,
    save,
  };
}
