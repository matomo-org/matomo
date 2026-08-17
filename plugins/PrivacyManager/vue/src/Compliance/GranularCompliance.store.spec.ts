/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

jest.mock('CoreHome', () => ({
  AjaxHelper: {
    fetch: jest.fn(),
    post: jest.fn(),
  },
}));

import { AjaxHelper } from 'CoreHome';
import {
  createGranularComplianceStore,
  PolicySetting,
  PolicySettingsPayload,
} from './GranularCompliance.store';

const fetchMock = AjaxHelper.fetch as jest.Mock;
const postMock = AjaxHelper.post as jest.Mock;

function setting(overrides: Partial<PolicySetting> = {}): PolicySetting {
  return {
    id: 'PrivacyManager.IPAnonymisation',
    name: 'IP Anonymisation Enabled',
    whatItDoes: 'what it does',
    impact: 'impact',
    status: 'non_compliant',
    enforced: false,
    toggleable: true,
    section: 'settings',
    ...overrides,
  };
}

function payload(
  settings: PolicySetting[],
  overrides: Partial<PolicySettingsPayload> = {},
): PolicySettingsPayload {
  return {
    policy: 'cnil_v1',
    title: 'CNIL',
    description: 'desc',
    configControlled: false,
    policyEnforced: false,
    settings,
    ...overrides,
  };
}

async function createLoadedStore(
  settings: PolicySetting[],
  overrides: Partial<PolicySettingsPayload> = {},
) {
  fetchMock.mockResolvedValueOnce(payload(settings, overrides));
  const store = createGranularComplianceStore('cnil_v1');
  store.setIdSite('1');
  await new Promise(process.nextTick);
  return store;
}

describe('PrivacyManager/GranularCompliance.store', () => {
  beforeEach(() => {
    fetchMock.mockReset();
    postMock.mockReset();
  });

  it('fetches the payload for the site and mirrors toggle state', async () => {
    const store = await createLoadedStore([
      setting({ enforced: true, status: 'enforced' }),
      setting({
        id: 'Core.ThirdPartyCookies',
        toggleable: false,
        enforced: null,
        section: 'external',
      }),
    ]);

    expect(fetchMock).toHaveBeenCalledWith(
      expect.objectContaining({
        method: 'PrivacyManager.getCompliancePolicySettings',
        idSite: '1',
        compliancePolicy: 'cnil_v1',
      }),
      expect.anything(),
    );
    expect(store.state.settings).toHaveLength(2);
    expect(store.state.localEnforced['PrivacyManager.IPAnonymisation']).toBe(true);
    // non-toggleable settings never get local toggle state
    expect('Core.ThirdPartyCookies' in store.state.localEnforced).toBe(false);
    expect(store.dirtySettingIds.value).toEqual([]);
  });

  it('tracks dirty settings when a toggle differs from the persisted state', async () => {
    const store = await createLoadedStore([setting({ enforced: false })]);

    store.toggleSetting('PrivacyManager.IPAnonymisation');
    expect(store.dirtySettingIds.value).toEqual(['PrivacyManager.IPAnonymisation']);

    // toggling back clears the dirty state
    store.toggleSetting('PrivacyManager.IPAnonymisation');
    expect(store.dirtySettingIds.value).toEqual([]);
  });

  it('ignores toggles for unknown settings and when config controlled', async () => {
    const store = await createLoadedStore(
      [setting({ enforced: true })],
      { configControlled: true, policyEnforced: true },
    );

    store.toggleSetting('PrivacyManager.IPAnonymisation');
    store.toggleSetting('PrivacyManager.DoesNotExist');

    expect(store.dirtySettingIds.value).toEqual([]);
  });

  it('enforceAll only flips local toggles and does not call the API', async () => {
    const store = await createLoadedStore([
      setting({ id: 'A.One', enforced: false }),
      setting({ id: 'B.Two', enforced: false }),
      setting({ id: 'C.External', toggleable: false, enforced: null, section: 'external' }),
    ]);

    store.enforceAll();

    expect(store.state.localEnforced['A.One']).toBe(true);
    expect(store.state.localEnforced['B.Two']).toBe(true);
    expect(store.dirtySettingIds.value).toEqual(['A.One', 'B.Two']);
    expect(postMock).not.toHaveBeenCalled();
  });

  it('save submits only the dirty settings and replaces state from the response', async () => {
    const store = await createLoadedStore([
      setting({ id: 'A.One', enforced: false }),
      setting({ id: 'B.Two', enforced: true }),
    ]);

    store.toggleSetting('A.One');
    postMock.mockResolvedValueOnce(payload([
      setting({ id: 'A.One', enforced: true, status: 'enforced' }),
      setting({ id: 'B.Two', enforced: true, status: 'enforced' }),
    ], { policyEnforced: true }));

    store.save('secret');
    await new Promise(process.nextTick);

    expect(postMock).toHaveBeenCalledWith(
      expect.objectContaining({ method: 'PrivacyManager.setCompliancePolicySettings' }),
      expect.objectContaining({
        settingValues: { 'A.One': 1 },
        passwordConfirmation: 'secret',
      }),
      expect.anything(),
    );
    expect(store.state.policyEnforced).toBe(true);
    expect(store.state.localEnforced['A.One']).toBe(true);
    expect(store.dirtySettingIds.value).toEqual([]);
    expect(store.state.saveSuccess).toBe(true);
  });

  it('clears the save success notification on the next toggle', async () => {
    const store = await createLoadedStore([
      setting({ id: 'A.One', enforced: false }),
    ]);

    store.toggleSetting('A.One');
    postMock.mockResolvedValueOnce(payload([
      setting({ id: 'A.One', enforced: true, status: 'enforced' }),
    ], { policyEnforced: true }));

    store.save('secret');
    await new Promise(process.nextTick);
    expect(store.state.saveSuccess).toBe(true);

    store.toggleSetting('A.One');
    expect(store.state.saveSuccess).toBe(false);
  });

  it('save without dirty settings is a no-op', async () => {
    const store = await createLoadedStore([setting()]);

    store.save('secret');

    expect(postMock).not.toHaveBeenCalled();
  });

  it('keeps local state and records the error when saving fails', async () => {
    const store = await createLoadedStore([setting({ id: 'A.One', enforced: false })]);

    store.toggleSetting('A.One');
    postMock.mockRejectedValueOnce(new Error('nope'));

    store.save('secret');
    await new Promise(process.nextTick);

    expect(store.state.saveError).toBe('nope');
    expect(store.state.saveSuccess).toBe(false);
    expect(store.dirtySettingIds.value).toEqual(['A.One']);
  });

  it('discards stale fetch responses after the site changed', async () => {
    let resolveFirst!: (p: PolicySettingsPayload) => void;
    fetchMock.mockImplementationOnce(() => new Promise((resolve) => { resolveFirst = resolve; }));
    fetchMock.mockResolvedValueOnce(payload([setting({ id: 'B.Two', enforced: true })]));

    const store = createGranularComplianceStore('cnil_v1');
    store.setIdSite('1');
    store.setIdSite('2');
    await new Promise(process.nextTick);

    // the slow response for site 1 arrives last and must be ignored
    resolveFirst(payload([setting({ id: 'A.One' })]));
    await new Promise(process.nextTick);

    expect(store.state.settings[0].id).toBe('B.Two');
  });

  it('records the error when fetching fails', async () => {
    fetchMock.mockRejectedValueOnce(new Error('offline'));
    const store = createGranularComplianceStore('cnil_v1');

    store.setIdSite('1');
    await new Promise(process.nextTick);

    expect(store.state.fetchError).toBe('offline');
    expect(store.state.loading).toBe(false);
  });
});
