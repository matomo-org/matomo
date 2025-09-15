import { DeepReadonly, reactive, readonly } from 'vue';
import { AjaxHelper } from 'CoreHome';

export interface ComplianceRequirement {
  name: string;
  value: string;
  notes: string;
}

interface ComplianceStatus {
  complianceModeEnforced: boolean;
  complianceRequirements: ComplianceRequirement[];
}

export interface CompliancePolicy {
  id: string;
  title: string;
  description: string;
}

interface ComplianceStoreState {
  idSite: string | null;
  loading: boolean;
  complianceType: string;
  complianceModeEnforced: boolean;
  complianceRequirements: ComplianceRequirement[];
  fetchComplianceError: string | null;
  saveComplianceError: string | null;
}

export interface ComplianceStore {
  state: DeepReadonly<ComplianceStoreState>;
  setIdSite: (idSite: string) => void;
  saveComplianceStatus: (enabled: boolean) => void;
}

export async function fetchCompliancePolicies(): Promise<CompliancePolicy[]> {
  return AjaxHelper.fetch<CompliancePolicy[]>(
    {
      method: 'PrivacyManager.getCompliancePolicies',
    },
    {
      createErrorNotification: false,
    },
  );
}

export function createComplianceStore(initialType: string): ComplianceStore {
  const state = reactive<ComplianceStoreState>({
    idSite: null,
    loading: false,
    complianceType: initialType,
    complianceModeEnforced: false,
    complianceRequirements: [],
    fetchComplianceError: null,
    saveComplianceError: null,
  });

  function fetchComplianceStatus(): Promise<ComplianceStatus> {
    return AjaxHelper.fetch<ComplianceStatus>(
      {
        idSite: state.idSite,
        complianceType: state.complianceType,
        method: 'PrivacyManager.getComplianceStatus',
      },
      {
        createErrorNotification: false,
      },
    );
  }

  function storeComplianceStatus(complianceData: ComplianceStatus) {
    state.complianceModeEnforced = complianceData.complianceModeEnforced;
    state.complianceRequirements = complianceData.complianceRequirements;
  }

  function fetchCompliance() {
    if (!state.idSite || !state.complianceType) return;
    state.loading = true;
    state.fetchComplianceError = null;
    fetchComplianceStatus().then((complianceData: ComplianceStatus) => {
      storeComplianceStatus(complianceData);
    }).catch((error) => {
      state.fetchComplianceError = error.message || error;
    }).finally(() => {
      state.loading = false;
    });
  }

  function setIdSite(idSite: string | null) {
    state.idSite = idSite;
    fetchCompliance();
  }

  function saveComplianceStatus(enforce: boolean) {
    state.loading = true;
    state.saveComplianceError = null;
    AjaxHelper.post<boolean>(
      {
        idSite: state.idSite,
        complianceType: state.complianceType,
        enforce,
        method: 'PrivacyManager.setComplianceStatus',
      },
      {
        createErrorNotification: false,
      },
    ).then(() => {
      fetchCompliance();
    }).catch((error) => {
      state.saveComplianceError = error.message || error;
    }).finally(() => {
      state.loading = false;
    });
  }

  const publicState = readonly(state) as DeepReadonly<ComplianceStoreState>;

  return {
    state: publicState,
    setIdSite,
    saveComplianceStatus,
  };
}
