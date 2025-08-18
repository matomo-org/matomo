import { DeepReadonly, reactive, readonly } from 'vue';

export interface ComplianceRequirement {
  name: string;
  value: string;
  notes: string
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

  function fetchCompliance() {
    if (!state.idSite || !state.complianceType) return;

    state.loading = true;

    setTimeout(() => {
      state.complianceModeEnforced = false;
      state.complianceRequirements = [
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

  function setIdSite(idSite: string | null) {
    state.idSite = idSite;
    fetchCompliance();
  }

  function saveComplianceStatus(enforce: boolean) {
    state.loading = true;

    setTimeout(() => {
      state.loading = false;

      state.complianceModeEnforced = enforce;

      state.complianceRequirements = [
        {
          name: 'IP Anonymisation',
          value: 'compliant',
          notes: 'Set to at least 2 byte masking',
        },
        {
          name: 'Data retention period',
          value: enforce ? 'compliant' : 'non_compliant',
          notes: 'Retention period is set to 365 days',
        },
        {
          name: 'Visits Log and Visitors Profile',
          value: enforce ? 'compliant' : 'non_compliant',
          notes: 'Visits log is still enabled',
        },
        {
          name: 'Ecommerce analytics',
          value: enforce ? 'compliant' : 'non_compliant',
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

  const publicState = readonly(state) as DeepReadonly<ComplianceStoreState>;

  return {
    state: publicState,
    setIdSite,
    saveComplianceStatus,
  };
}
