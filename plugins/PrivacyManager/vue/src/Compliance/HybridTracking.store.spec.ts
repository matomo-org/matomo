/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

vi.mock('CoreHome', () => ({
  AjaxHelper: {
    fetch: vi.fn(),
    post: vi.fn(),
  },
}));

import { AjaxHelper } from 'CoreHome';
import { createHybridTrackingStore, HybridTrackingPayload } from './HybridTracking.store';

const fetchMock = vi.mocked(AjaxHelper.fetch);
const postMock = vi.mocked(AjaxHelper.post);

function payload(overrides: Partial<HybridTrackingPayload> = {}): HybridTrackingPayload {
  return {
    enabled: false,
    enabledForAllWebsites: false,
    isWritable: true,
    ...overrides,
  };
}

async function createLoadedStore(overrides: Partial<HybridTrackingPayload> = {}, idSite = '1') {
  fetchMock.mockResolvedValueOnce(payload(overrides));
  const store = createHybridTrackingStore();
  store.setIdSite(idSite);
  await new Promise(process.nextTick);
  return store;
}

describe('PrivacyManager/HybridTracking.store', () => {
  beforeEach(() => {
    fetchMock.mockReset();
    postMock.mockReset();
  });

  it('fetches the value of the scope', async () => {
    const store = await createLoadedStore({ enabled: true }, 'all');

    expect(fetchMock).toHaveBeenCalledWith(
      { idSite: 'all', method: 'PrivacyManager.getConsentAwareAnalytics' },
      { createErrorNotification: false },
    );
    expect(store.state.enabled).toBe(true);
    expect(store.state.localEnabled).toBe(true);
    expect(store.hasUnsavedChanges.value).toBe(false);
  });

  it('keeps a toggle unsaved until it is saved', async () => {
    const store = await createLoadedStore();

    store.toggle();

    expect(store.state.localEnabled).toBe(true);
    expect(store.hasUnsavedChanges.value).toBe(true);
  });

  it('cannot be toggled when the value is not writable', async () => {
    const store = await createLoadedStore({ isWritable: false });

    store.toggle();

    expect(store.state.localEnabled).toBe(false);
  });

  it('posts the value as a number with the password', async () => {
    const store = await createLoadedStore();
    postMock.mockResolvedValueOnce(payload({ enabled: true }));

    store.toggle();
    store.save('secret');
    await new Promise(process.nextTick);

    expect(postMock).toHaveBeenCalledWith(
      { idSite: '1', method: 'PrivacyManager.setConsentAwareAnalytics' },
      { enabled: 1, passwordConfirmation: 'secret' },
      { createErrorNotification: false },
    );
    expect(store.state.enabled).toBe(true);
    expect(store.state.saveSuccess).toBe(true);
    expect(store.hasUnsavedChanges.value).toBe(false);
  });

  it('posts nothing when nothing changed', async () => {
    const store = await createLoadedStore();

    store.save('secret');

    expect(postMock).not.toHaveBeenCalled();
  });

  it('reports a failed save and keeps the change', async () => {
    const store = await createLoadedStore();
    postMock.mockRejectedValueOnce(new Error('Wrong password'));

    store.toggle();
    store.save('wrong');
    await new Promise(process.nextTick);

    expect(store.state.saveError).toBe('Wrong password');
    expect(store.hasUnsavedChanges.value).toBe(true);
  });

  it('clears the save outcome and ignores a late response when the site changes', async () => {
    const store = await createLoadedStore();
    postMock.mockResolvedValueOnce(payload({ enabled: true }));
    store.toggle();
    store.save('secret');
    await new Promise(process.nextTick);
    expect(store.state.saveSuccess).toBe(true);

    let resolveFirst: (value: HybridTrackingPayload) => void = () => {};
    fetchMock.mockReturnValueOnce(new Promise((resolve) => { resolveFirst = resolve; }));
    fetchMock.mockResolvedValueOnce(payload({ enabledForAllWebsites: true }));

    store.setIdSite('2');
    expect(store.state.saveSuccess).toBe(false);
    store.setIdSite('3');
    await new Promise(process.nextTick);
    resolveFirst(payload({ enabled: true }));
    await new Promise(process.nextTick);

    expect(store.state.idSite).toBe('3');
    expect(store.state.enabled).toBe(false);
    expect(store.state.enabledForAllWebsites).toBe(true);
  });
});
