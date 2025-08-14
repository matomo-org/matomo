import { DeepReadonly, reactive, readonly } from 'vue';
import { AjaxHelper } from 'CoreHome';

export interface ComplianceRequirement {
  name: string;
  value: string;
  notes: string
}

interface ComplianceStatus {
  complianceModeEnforced: boolean;
  complianceRequirements: ComplianceRequirement[];
}

interface ComplianceStoreState {
  idSite: string | null;
  loading: boolean;
  complianceType: string;
  complianceModeEnforced: boolean;
  complianceRequirements: ComplianceRequirement[];
}

export interface ComplianceStore {
  state: DeepReadonly<ComplianceStoreState>;
  setIdSite: (idSite: string) => void;
  saveComplianceStatus: (enabled: boolean) => void;
}

export function createComplianceStore(initialType: string): ComplianceStore {
  const state = reactive<ComplianceStoreState>({
    idSite: null,
    loading: false,
    complianceType: initialType,
    complianceModeEnforced: false,
    complianceRequirements: [],
  });

  function fetchComplianceStatus(): Promise<ComplianceStatus> {
    return AjaxHelper.fetch<ComplianceStatus>({
      idSite: state.idSite,
      complianceType: state.complianceType,
      method: 'PrivacyManager.getComplianceStatus',
    });
  }

  function storeComplianceStatus(complianceData: ComplianceStatus) {
    state.complianceModeEnforced = complianceData.complianceModeEnforced;
    state.complianceRequirements = complianceData.complianceRequirements;
  }

  function fetchCompliance() {
    if (!state.idSite || !state.complianceType) return;
    state.loading = true;
    fetchComplianceStatus().then((complianceData: ComplianceStatus) => {
      storeComplianceStatus(complianceData);
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
    AjaxHelper.fetch<boolean>({
      idSite: state.idSite,
      complianceType: state.complianceType,
      enforce,
      method: 'PrivacyManager.setComplianceStatus',
    }).then(() => {
      fetchComplianceStatus().then((res) => {
        res.complianceModeEnforced = enforce;
        // below logic will be replaced with internal logic in PrivacyManager.getComplianceStatus
        if (enforce) {
          res.complianceRequirements = res.complianceRequirements.map((indicator) => {
            indicator.value = 'compliant';
            return indicator;
          });
        }
        storeComplianceStatus(res);
      });
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
