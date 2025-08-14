import { DeepReadonly, reactive, readonly } from 'vue';
import { AjaxHelper } from 'CoreHome';

export interface ComplianceRequirement {
  name: string;
  value: string;
  notes: string
}

interface ComplianceStatus {
  complianceModeEnabled: boolean;
  complianceIndicators: ComplianceRequirement[];
}

interface ComplianceStoreState {
  idsite: string | null;
  loading: boolean;
  compliance_type: string;
  compliance_mode_enforced: boolean;
  complicance_requirements: ComplianceRequirement[];
}

export interface ComplianceStore {
  state: DeepReadonly<ComplianceStoreState>;
  setIdSite: (idsite: string) => void;
  saveComplianceStatus: (enabled: boolean) => void;
}

export function createComplianceStore(initialType: string): ComplianceStore {
  const state = reactive<ComplianceStoreState>({
    idsite: null,
    loading: false,
    compliance_type: initialType,
    compliance_mode_enforced: false,
    complicance_requirements: [],
  });

  function fetchComplianceStatus(): Promise<ComplianceStatus> {
    return AjaxHelper.fetch<ComplianceStatus>({
      idSite: state.idsite,
      complianceType: state.compliance_type,
      method: 'PrivacyManager.getComplianceStatus',
    });
  }

  function storeComplianceStatus(complianceData: ComplianceStatus) {
    state.compliance_mode_enabled = complianceData.complianceModeEnabled;
    state.compliance_indicators = complianceData.complianceIndicators;
  }

  function fetchCompliance() {
    if (!state.idsite || !state.compliance_type) return;
    state.loading = true;
    fetchComplianceStatus().then((complianceData: ComplianceStatus) => {
      storeComplianceStatus(complianceData);
    }).finally(() => {
      state.loading = false;
    });
  }

  function setIdSite(idSite: string | null) {
    state.idsite = idSite;
    fetchCompliance();
  }

  function saveComplianceStatus(enabled: boolean) {
    state.loading = true;
    AjaxHelper.fetch<boolean>({
      idSite: state.idsite,
      complianceType: state.compliance_type,
      enabled,
      method: 'PrivacyManager.setComplianceStatus',
    }).then(() => {
        fetchComplianceStatus().then((res) => {
          res.complianceModeEnabled = enabled;
          // below logic will be replaced with internal logic in PrivacyManager.getComplianceStatus
          if (enabled) {
            res.complianceIndicators = res.complianceIndicators.map((indicator) => {
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
