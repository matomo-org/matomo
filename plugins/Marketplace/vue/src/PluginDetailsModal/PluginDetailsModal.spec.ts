/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import type { Mock } from 'vitest';

const { mockPost, modalApi } = vi.hoisted(() => {
  // a chainable jQuery stand-in. `each` and `attr` are no-ops for the link rewriting the component
  // schedules on a timer after mounting, which would otherwise throw once each test has finished.
  const api: { modal: Mock; each: Mock; attr: Mock; length: number } = {
    modal: vi.fn(),
    each: vi.fn(),
    attr: vi.fn(),
    length: 0,
  };
  api.modal.mockReturnValue(api);
  api.each.mockReturnValue(api);

  // the component reads window.$ at module load, so the stub has to exist before it is imported
  (window as unknown as Record<string, unknown>).$ = vi.fn(() => api);

  return { mockPost: vi.fn(), modalApi: api };
});

vi.mock('CoreHome', () => ({
  ActivityIndicator: { template: '<div class="activityIndicator" />' },
  AjaxHelper: {
    post: mockPost,
  },
  MatomoUrl: {
    hashParsed: { value: {} },
    updateHash: vi.fn(),
  },
  translate: (key: string) => key,
  externalLink: (url: string) => `<a href="${url}">`,
}));

/* eslint-disable import/first */
import PluginDetailsModal from './PluginDetailsModal.vue';

// a row exactly as Controller::keepPluginCardFields() leaves it: no shop, versions or screenshots
const cardRow = {
  name: 'PaidPlugin1',
  displayName: 'Paid Plugin 1',
  description: 'A paid plugin',
  owner: 'matomo-org',
  coverImage: 'cover.png',
  isFree: false,
  isPaid: true,
  isInstalled: false,
  isActivated: false,
  isInvalid: false,
  isDownloadable: false,
  canBeUpdated: false,
  hasDownloadLink: false,
  hasExceededLicense: false,
  isMissingLicense: false,
  isEligibleForFreeTrial: false,
  isTrialRequested: false,
  canTrialBeRequested: false,
  missingRequirements: [],
  numDownloads: 12,
  numDownloadsPretty: '12',
  priceFrom: null,
  consumer: {},
};

const detailsResponse = {
  ...cardRow,
  isBundle: false,
  latestVersion: '1.2.3',
  lastUpdated: 'today',
  licenseStatus: '',
  homepage: null,
  repositoryUrl: null,
  keywords: [],
  authors: [],
  support: [],
  activity: {},
  changelog: {},
  screenshots: [],
  shop: { url: 'https://shop.example', variations: [], reviews: {} },
  versions: [{ name: '1.2.3', readmeHtml: { description: '<p>readme</p>' } }],
};

function mountModal() {
  return mount(PluginDetailsModal, {
    props: {
      modelValue: null,
      activateNonce: 'a',
      deactivateNonce: 'd',
      installNonce: 'i',
      updateNonce: 'u',
      isAutoUpdatePossible: true,
      isValidConsumer: true,
      isMultiServerEnvironment: false,
      isPluginsAdminEnabled: true,
      isSuperUser: true,
      hasSomeAdminAccess: true,
      numUsers: 1,
    },
    global: {
      mocks: {
        // the template resolves these off the render context, not the module imports
        translate: (key: string) => key,
        $sanitize: (value: string) => value,
        externalRawLink: (url: string) => url,
        externalLink: (url: string) => url,
      },
      stubs: {
        CTAContainer: true,
        MissingReqsNotice: true,
      },
    },
  });
}

// require() yields an untyped component, so name the parts of the instance the specs assert on
interface ModalVm {
  isLoading: boolean;
  fetchErrorMessage: string;
  plugin: Record<string, unknown>;
  pluginShopVariations: unknown[];
  pluginScreenshots: unknown[];
  pluginChangelogUrl: string;
}

function vmOf(wrapper: { vm: unknown }): ModalVm {
  return wrapper.vm as ModalVm;
}

async function flushPromises() {
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
}

describe('PluginDetailsModal', () => {
  beforeEach(() => {
    mockPost.mockReset();
    modalApi.modal.mockClear();
  });

  it('requests the plugin details when it opens', async () => {
    mockPost.mockReturnValue(new Promise(() => { /* never settles */ }));

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });

    expect(mockPost).toHaveBeenCalledTimes(1);
    expect(mockPost.mock.calls[0][0]).toEqual({
      module: 'Marketplace',
      action: 'getPluginDetails',
      format: 'JSON',
    });
    expect(mockPost.mock.calls[0][1]).toEqual({ pluginName: 'PaidPlugin1' });
    // the modal covers the page, so an error notification behind it would never be seen
    expect(mockPost.mock.calls[0][2].createErrorNotification).toBe(false);
  });

  it('shows a loading box while the request is in flight, not an empty modal', async () => {
    // the overlay opens as soon as a card is clicked, so rendering nothing until the response
    // arrives reads as a darkened screen with no modal on it
    let resolveRequest: (value: unknown) => void = () => {};
    mockPost.mockReturnValue(new Promise((resolve) => {
      resolveRequest = resolve;
    }));

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });

    expect(vmOf(wrapper).isLoading).toBe(true);
    expect(wrapper.find('.modal-content--loading').exists()).toBe(true);
    expect(wrapper.find('.activityIndicator').exists()).toBe(true);

    resolveRequest(detailsResponse);
    await flushPromises();

    expect(vmOf(wrapper).isLoading).toBe(false);
    expect(wrapper.find('.modal-content--loading').exists()).toBe(false);
    expect(wrapper.find('.modal-content').exists()).toBe(true);
  });

  it('merges the fetched details over the card row', async () => {
    mockPost.mockResolvedValue(detailsResponse);

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });
    await flushPromises();

    expect(vmOf(wrapper).isLoading).toBe(false);
    expect(vmOf(wrapper).fetchErrorMessage).toBe('');
    expect((vmOf(wrapper).plugin.shop as { url: string }).url).toBe('https://shop.example');
    expect(vmOf(wrapper).pluginShopVariations).toEqual([]);
    // the card row still supplies what the details response does not repeat
    expect(vmOf(wrapper).plugin.displayName).toBe('Paid Plugin 1');
  });

  it('keeps the card row and shows the error when the request fails', async () => {
    mockPost.mockRejectedValue({ message: 'There was an error reading the response' });

    const wrapper = mountModal();
    // trial-eligible, so the free-trial footer renders: that is the part of the template which
    // reads shop variations, and the card row deliberately carries no shop at all
    await wrapper.setProps({ modelValue: { ...cardRow, isEligibleForFreeTrial: true } });
    await flushPromises();

    expect(wrapper.find('.modal-content').exists()).toBe(true);

    expect(vmOf(wrapper).isLoading).toBe(false);
    expect(vmOf(wrapper).fetchErrorMessage).toBe('There was an error reading the response');
    // whatever the card already knew must survive, and the fields only the details response
    // carries must stay absent rather than throwing while the template renders
    expect(vmOf(wrapper).plugin.displayName).toBe('Paid Plugin 1');
    expect(vmOf(wrapper).plugin.shop).toBeUndefined();
    expect(vmOf(wrapper).pluginShopVariations).toEqual([]);
    expect(vmOf(wrapper).pluginScreenshots).toEqual([]);
    expect(vmOf(wrapper).pluginChangelogUrl).toBe('');
  });

  it('falls back to a generic message when the failure carries none', async () => {
    mockPost.mockRejectedValue({});

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });
    await flushPromises();

    expect(vmOf(wrapper).fetchErrorMessage).toBe('General_ErrorRequest');
  });

  it('aborts a request still in flight when another plugin is opened', async () => {
    mockPost.mockReturnValue(new Promise(() => { /* never settles */ }));

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });

    const firstController = mockPost.mock.calls[0][2].abortController;
    const abortSpy = vi.spyOn(firstController, 'abort');

    await wrapper.setProps({ modelValue: { ...cardRow, name: 'PaidPlugin2' } });

    expect(abortSpy).toHaveBeenCalled();
    expect(mockPost).toHaveBeenCalledTimes(2);
    expect(mockPost.mock.calls[1][1]).toEqual({ pluginName: 'PaidPlugin2' });
  });

  it('does not carry one plugin\'s details into the next when the new request fails', async () => {
    mockPost.mockResolvedValueOnce(detailsResponse);

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });
    await flushPromises();
    expect((vmOf(wrapper).plugin.shop as { url: string }).url).toBe('https://shop.example');

    mockPost.mockRejectedValueOnce({ message: 'nope' });
    await wrapper.setProps({ modelValue: { ...cardRow, name: 'PaidPlugin2', displayName: 'Two' } });
    await flushPromises();

    // the second plugin's card data must not be shown beside the first plugin's shop and versions
    expect(vmOf(wrapper).plugin.displayName).toBe('Two');
    expect(vmOf(wrapper).plugin.shop).toBeUndefined();
    expect(vmOf(wrapper).plugin.versions).toBeUndefined();
  });

  it('ignores a successful response that has already been superseded', async () => {
    let resolveFirst: (value: unknown) => void = () => undefined;
    mockPost
      .mockReturnValueOnce(new Promise((resolve) => { resolveFirst = resolve; }))
      .mockReturnValue(new Promise(() => { /* never settles */ }));

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });
    await wrapper.setProps({ modelValue: { ...cardRow, name: 'PaidPlugin2', displayName: 'Two' } });

    // the first plugin's details arrive late; they belong to a plugin no longer being looked at
    resolveFirst(detailsResponse);
    await flushPromises();

    expect(vmOf(wrapper).plugin.displayName).toBe('Two');
    expect(vmOf(wrapper).plugin.shop).toBeUndefined();
    expect(vmOf(wrapper).isLoading).toBe(true);
  });

  it('does not resolve the loading state from a superseded request', async () => {
    let rejectFirst: (reason?: unknown) => void = () => undefined;
    mockPost
      .mockReturnValueOnce(new Promise((resolve, reject) => { rejectFirst = reject; }))
      .mockReturnValue(new Promise(() => { /* never settles */ }));

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });
    await wrapper.setProps({ modelValue: { ...cardRow, name: 'PaidPlugin2' } });

    // the first request rejects only after being superseded, so it must not clear the spinner
    // or report an error belonging to a plugin the user is no longer looking at
    rejectFirst({ message: 'stale failure' });
    await flushPromises();

    expect(vmOf(wrapper).isLoading).toBe(true);
    expect(vmOf(wrapper).fetchErrorMessage).toBe('');
  });
});
