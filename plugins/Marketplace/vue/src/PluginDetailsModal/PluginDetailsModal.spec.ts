/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const mockPost = jest.fn();

jest.mock('CoreHome', () => ({
  AjaxHelper: {
    post: mockPost,
  },
  MatomoUrl: {
    hashParsed: { value: {} },
    updateHash: jest.fn(),
  },
  translate: (key: string) => key,
  externalLink: (url: string) => `<a href="${url}">`,
  // rendered by the loading branch; without it every mount logs a resolve warning
  MatomoLoader: { name: 'MatomoLoader', template: '<div class="matomo-loader" />' },
}), { virtual: true });

// the component reads window.$ at module load, so the stub has to exist before it is required
const modalApi: { modal: jest.Mock } = { modal: jest.fn(() => modalApi) };
(window as unknown as Record<string, unknown>).$ = jest.fn(() => modalApi);

// eslint-disable-next-line @typescript-eslint/no-var-requires
const PluginDetailsModal = require('./PluginDetailsModal.vue').default;

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

function mountModal(renderCta = false) {
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
        ...(renderCta ? {} : { CTAContainer: true }),
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

  it('offers no purchase link when the details the shop URL comes from could not be fetched', () => {
    mockPost.mockRejectedValue({ message: 'There was an error reading the response' });

    // CTAContainer is rendered for real here: the card row alone cannot supply a shop variation,
    // so an unguarded "add to cart" would link to the empty string and reload the page
    const wrapper = mountModal(true);

    return wrapper.setProps({ modelValue: { ...cardRow, isEligibleForFreeTrial: true } })
      .then(flushPromises)
      .then(() => {
        expect(wrapper.find('.alert-danger').text()).toContain('There was an error reading');
        expect(wrapper.find('.addToCartLink').exists()).toBe(false);
        expect(wrapper.find('.free-trial-dropdown').exists()).toBe(false);
      });
  });

  it('offers the purchase link once the details carry a shop variation', () => {
    mockPost.mockResolvedValue({
      ...detailsResponse,
      isEligibleForFreeTrial: true,
      shop: {
        url: 'https://shop.example',
        variations: [{
          name: 'Business',
          prettyPrice: '$100',
          period: 'year',
          price: 100,
          currency: 'USD',
          recommended: true,
          addToCartUrl: 'https://shop.example/cart',
        }],
        reviews: {},
      },
    });

    const wrapper = mountModal(true);

    return wrapper.setProps({ modelValue: { ...cardRow, isEligibleForFreeTrial: true } })
      .then(flushPromises)
      .then(() => {
        expect(wrapper.find('.addToCartLink').attributes('href')).toBe('https://shop.example/cart');
        expect(wrapper.find('.free-trial-dropdown').exists()).toBe(true);
      });
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
    const abortSpy = jest.spyOn(firstController, 'abort');

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

  it('renders the loader instead of the details while the request is in flight', async () => {
    mockPost.mockReturnValue(new Promise(() => { /* never settles */ }));

    const wrapper = mountModal();
    await wrapper.setProps({ modelValue: cardRow });

    expect(wrapper.find('.modal-content--loading').exists()).toBe(true);
    expect(wrapper.find('.matomo-loader').exists()).toBe(true);
    expect(wrapper.find('.modal-content__main').exists()).toBe(false);
  });
});
