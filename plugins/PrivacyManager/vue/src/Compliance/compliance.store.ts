import { reactive, readonly } from 'vue';

export interface ComplianceIndicator {
  name: string;
  value: string;
  notes: string
}

interface ComplianceStoreState {
  idsite: string | null;
  loading: boolean;
  compliance_type: string;
  compliance_mode_enabled: boolean;
  compliance_indicators: ComplianceIndicator[];
}

export interface ComplianceStore {
  state: ComplianceStoreState;
  setIdSite: (idsite: int) => void;
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

    setTimeout(() => {
      state.compliance_mode_enabled = false;
      state.compliance_indicators = [
        {
          name: 'IP Anonymisation',
          value: 'compliant',
          notes: 'Set to at least 2 byte masking',
        },
        {
          name: 'Data retention period',
          value: 'non_compliant',
          notes: 'Retention period is set to 365 days',
        },
        {
          name: 'Visits Log and Visitors Profile',
          value: 'non_compliant',
          notes: 'Visits log is still enabled',
        },
        {
          name: 'Ecommerce analytics',
          value: 'non_compliant',
          notes: 'Ecommerce analytics is enabled for this site',
        },
        {
          name: 'Opt out',
          value: 'unknown',
          notes: 'Opt out must be manually set up and configured',
        },
      ];

      state.loading = false;
    }, Math.floor(Math.random() * 1200) + 300);
  }

  function setIdSite(idSite: int | string | null) {
    state.idsite = idSite;
    fetchCompliance();
  }

  function saveComplianceStatus(enabled: boolean) {
    state.loading = true;

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
  }

  return {
    state: readonly(state),
    setIdSite,
    saveComplianceStatus,
  };
}
