import { DeepReadonly, reactive, readonly } from 'vue';
import { AjaxHelper } from 'CoreHome';

export interface ComplianceRequirement {
  name: string;
  value: string;
  notes: string
}

interface ComplianceStatus {
  complianceModeEnabled: boolean;
  complianceIndicators: ComplianceIndicator[];
}

interface ComplianceStoreState {
  idsite: string | null;
  loading: boolean;
  compliance_type: string;
  compliance_mode_enabled: boolean;
  compliance_indicators: ComplianceRequirement[];
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
    compliance_mode_enabled: false,
    compliance_indicators: [],
  });

  function fetchCompliance() {
    if (!state.idsite || !state.compliance_type) return;

    state.loading = true;

    AjaxHelper.fetch<ComplianceStatus>({
      idSite: state.idsite,
      complianceType: state.compliance_type,
      method: 'PrivacyManager.getComplianceStatus',
    }).then((response: ComplianceStatus) => {
        state.compliance_mode_enabled = response.complianceModeEnabled;
        state.compliance_indicators = response.complianceIndicators;
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
      enabled: enabled,
      method: 'PrivacyManager.setComplianceStatus'
    }).then((response: boolean) => {
      state.compliance_mode_enabled = response;
      for (let indicator of state.compliance_indicators) {
        indicator.value = state.compliance_mode_enabled ? 'compliant' : 'non_compliant';
      }
    }).finally(() => {
      state.loading = false;
    });
    /*
    setTimeout(() => {
      state.loading = false;

      state.compliance_mode_enabled = enabled;

      state.compliance_indicators = [
        {
          name: 'IP Anonymisation',
          value: 'compliant',
          notes: 'Set to at least 2 byte masking',
        },
        {
          name: 'Data retention period',
          value: enabled ? 'compliant' : 'non_compliant',
          notes: 'Retention period is set to 365 days',
        },
        {
          name: 'Visits Log and Visitors Profile',
          value: enabled ? 'compliant' : 'non_compliant',
          notes: 'Visits log is still enabled',
        },
        {
          name: 'Ecommerce analytics',
          value: enabled ? 'compliant' : 'non_compliant',
          notes: 'Ecommerce analytics is enabled for this site',
        },
        {
          name: 'Opt out',
          value: 'unknown',
          notes: 'Opt out must be manually set up and configured',
        },
      ];
    }, Math.floor(Math.random() * 1200) + 300);
    */
  }

  const publicState = readonly(state) as DeepReadonly<ComplianceStoreState>;

  return {
    state: publicState,
    setIdSite,
    saveComplianceStatus,
  };
}
