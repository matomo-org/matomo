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
  save: (password: string) => Promise<boolean>;
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
    if (!state.isWritable || state.saving) {
      return;
    }

    state.localEnabled = !state.localEnabled;
    state.saveSuccess = false;
  }

  function save(password: string): Promise<boolean> {
    if (!hasUnsavedChanges.value || state.saving) {
      return Promise.resolve(false);
    }

    // guard against applying a save response after the site changed mid-request
    const requestedIdSite = state.idSite;

    state.saving = true;
    state.saveError = null;
    state.saveSuccess = false;
    return AjaxHelper.post<HybridTrackingPayload>(
      {
        idSite: requestedIdSite,
        method: 'PrivacyManager.setConsentAwareAnalytics',
      },
      {
        // posted as 1 or 0, like the compliance policy settings
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
      } else {
        // the new scope may have been fetched before this save landed, e.g. whether it is on
        // for all websites
        fetch();
      }

      return true;
    }).catch((error) => {
      if (state.idSite === requestedIdSite) {
        state.saveError = error.message || error;
      }

      return false;
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
