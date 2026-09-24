/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const { mockPost } = vi.hoisted(() => ({ mockPost: vi.fn() }));

vi.mock('CoreHome', () => ({
  // the screenshot lightbox; rendered inline here rather than teleported to the document body,
  // so `wrapper.find` can still see what it was handed
  MatomoModal: {
    props: ['modelValue'],
    template: '<div class="matomoModal" v-if="modelValue"><slot /></div>',
  },
  AjaxHelper: {
    post: mockPost,
  },
  // needed by the ShopPricing block the footer renders once the details carry a variation.
  // en-US grouping is enough here; the real formatter is locale driven and covered elsewhere
  NumberFormatter: {
    formatNumber: (value: number, max: number, min: number) => Number(value).toLocaleString(
      'en-US',
      { maximumFractionDigits: max, minimumFractionDigits: min },
    ),
  },
  translate: (key: string) => key,
  externalLink: (url: string) => `<a href="${url}">`,
}));

/* eslint-disable import/first */
import PluginDetails from './PluginDetails.vue';

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

function mountDetails(pluginCard: Record<string, unknown>, renderCta = false) {
  return mount(PluginDetails, {
    props: {
      pluginCard,
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
interface DetailsVm {
  isLoading: boolean;
  fetchErrorMessage: string;
  plugin: Record<string, unknown>;
  pluginShopVariations: unknown[];
  pluginScreenshots: unknown[];
  pluginChangelogUrl: string;
}

function vmOf(wrapper: { vm: unknown }): DetailsVm {
  return wrapper.vm as DetailsVm;
}

async function flushPromises() {
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
}

describe('PluginDetails', () => {
  beforeEach(() => {
    mockPost.mockReset();
  });

  it('requests the plugin details when it opens', async () => {
    mockPost.mockReturnValue(new Promise(() => { /* never settles */ }));

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();

    expect(mockPost).toHaveBeenCalledTimes(1);
    expect(mockPost.mock.calls[0][0]).toEqual({
      module: 'Marketplace',
      action: 'getPluginDetails',
      format: 'JSON',
    });
    expect(mockPost.mock.calls[0][1]).toEqual({ pluginName: 'PaidPlugin1' });
    // the page renders the failure itself, so a notification would only repeat it
    expect(mockPost.mock.calls[0][2].createErrorNotification).toBe(false);
  });

  it('holds the page\'s shape while the request is in flight', async () => {
    // the page replaces the catalogue as soon as a card is clicked, so rendering nothing until
    // the response arrives would read as the marketplace having vanished. A skeleton rather than
    // a spinner, as the catalogue's own cards use.
    let resolveRequest: (value: unknown) => void = () => {};
    mockPost.mockReturnValue(new Promise((resolve) => {
      resolveRequest = resolve;
    }));

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();

    expect(vmOf(wrapper).isLoading).toBe(true);
    expect(wrapper.find('.pluginDetailsSkeleton').exists()).toBe(true);

    resolveRequest(detailsResponse);
    await flushPromises();

    expect(vmOf(wrapper).isLoading).toBe(false);
    expect(wrapper.find('.pluginDetailsSkeleton').exists()).toBe(false);
    expect(wrapper.find('.marketplacePluginDetails__head').exists()).toBe(true);
  });

  it('merges the fetched details over the card row', async () => {
    mockPost.mockResolvedValue(detailsResponse);

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();
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

    // trial-eligible, so the purchase panel renders: that is the part of the template which
    // reads shop variations, and the card row deliberately carries no shop at all
    const wrapper = mountDetails({ ...cardRow, isEligibleForFreeTrial: true });
    await flushPromises();

    expect(wrapper.find('.marketplacePluginDetails__head').exists()).toBe(true);

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

  it('offers no purchase link when the details the shop URL comes from could not be fetched', async () => {
    mockPost.mockRejectedValue({ message: 'There was an error reading the response' });

    // CTAContainer is rendered for real here: the card row alone cannot supply a shop variation,
    // so an unguarded "add to cart" would link to the empty string and reload the page
    const wrapper = mountDetails({ ...cardRow, isEligibleForFreeTrial: true }, true);
    await wrapper.vm.$nextTick();
    await flushPromises();

    expect(wrapper.find('.alert-danger').text()).toContain('There was an error reading');
    expect(wrapper.find('.addToCartLink').exists()).toBe(false);
    expect(wrapper.find('.shopPricing').exists()).toBe(false);
  });

  it('offers the purchase link once the details carry a shop variation', async () => {
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

    const wrapper = mountDetails({ ...cardRow, isEligibleForFreeTrial: true }, true);
    await wrapper.vm.$nextTick();
    await flushPromises();

    expect(wrapper.find('.addToCartLink').attributes('href')).toBe('https://shop.example/cart');
    // the free trial dropdown this used to pin is now the ShopPricing block, and its cart button
    // is what tells a trial-eligible visitor the price they are looking at starts as a trial
    expect(wrapper.find('.shopPricing').exists()).toBe(true);
    expect(wrapper.find('.addToCartLink').text()).toBe('Marketplace_StartFree30DayTrial');
  });

  it('shows the screenshot gallery and opens one in the lightbox', async () => {
    mockPost.mockResolvedValue({
      ...detailsResponse,
      screenshots: ['https://example.org/shots/Heatmap_overview.png'],
    });

    const wrapper = mountDetails(cardRow, true);
    await flushPromises();

    const shots = wrapper.findAll('.marketplacePluginDetails__shotButton');
    expect(shots.length).toBe(1);
    expect(wrapper.find('.marketplacePluginDetails__shotCaption').text()).toBe('Heatmap overview');
    expect(wrapper.find('.matomoModal').exists()).toBe(false);

    await shots[0].trigger('click');

    expect(wrapper.find('.matomoModal').exists()).toBe(true);
    expect(wrapper.find('.marketplacePluginDetails__lightboxImage').attributes('src'))
      .toBe('https://example.org/shots/Heatmap_overview.png');
  });

  it('leaves out the reviews card when the plugin has no reviews', async () => {
    mockPost.mockResolvedValue(detailsResponse);

    const wrapper = mountDetails(cardRow, true);
    await flushPromises();

    expect(wrapper.find('.marketplacePluginDetails__reviews').exists()).toBe(false);
  });

  it('heads the reviews card with the score and how many it averages', async () => {
    mockPost.mockResolvedValue({
      ...detailsResponse,
      shop: {
        ...detailsResponse.shop,
        reviews: {
          embedUrl: 'https://shop.example/reviews',
          averageRating: '4.50',
          reviewCount: 3,
          ratingCount: 7,
        },
      },
    });

    const wrapper = mountDetails(cardRow, true);
    await flushPromises();

    expect(wrapper.find('.marketplacePluginDetails__reviewScore').text()).toBe('4.50');
    // reviewCount, the written reviews the embed lists - not ratingCount, which also counts
    // scores left without one
    expect(wrapper.find('.marketplacePluginDetails__reviewCount').text())
      .toBe('Marketplace_NumReviews');
  });

  it('drops the count rather than showing a zero when the shop sent none', async () => {
    mockPost.mockResolvedValue({
      ...detailsResponse,
      shop: {
        ...detailsResponse.shop,
        reviews: { embedUrl: 'https://shop.example/reviews', averageRating: '5.00' },
      },
    });

    const wrapper = mountDetails(cardRow, true);
    await flushPromises();

    expect(wrapper.find('.marketplacePluginDetails__reviews').exists()).toBe(true);
    expect(wrapper.find('.marketplacePluginDetails__reviewCount').exists()).toBe(false);
    // nor a link in the head with no text to read out
    expect(wrapper.find('.marketplacePluginDetails__reviewsLink').exists()).toBe(false);
  });

  it('opens the readme\'s own links in a new tab', async () => {
    mockPost.mockResolvedValue({
      ...detailsResponse,
      versions: [{
        name: '1.2.3',
        readmeHtml: {
          description: '<p><a href="https://example.org/docs">docs</a> <a href="#faq">faq</a></p>',
        },
      }],
    });

    const wrapper = mountDetails(cardRow, true);
    await flushPromises();
    // one tick for the render the loaded state triggers, and one for the rewrite queued behind it
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();

    const [external, anchor] = wrapper.findAll('.marketplacePluginDetails__readme a');
    expect(external.attributes('target')).toBe('_blank');
    expect(external.attributes('rel')).toBe('noreferrer noopener');
    // a link within the readme itself stays in the page
    expect(anchor.attributes('target')).toBeUndefined();
  });

  it('prices nothing for a plugin that is already installed', async () => {
    mockPost.mockResolvedValue(detailsResponse);

    const wrapper = mountDetails({ ...cardRow, isInstalled: true, isPaid: true }, true);
    await flushPromises();

    // purchased or free, the panel is the status and its button alone
    expect(wrapper.find('.shopPricing').exists()).toBe(false);
    expect(wrapper.find('.marketplacePluginDetails__free').exists()).toBe(false);
    expect(wrapper.find('.marketplacePluginDetails__cta').exists()).toBe(true);
  });

  it('says Free above the button for a plugin that costs nothing', async () => {
    // the details response is merged over the card row, so it has to say free as well
    mockPost.mockResolvedValue({ ...detailsResponse, isPaid: false });

    const wrapper = mountDetails({ ...cardRow, isPaid: false }, true);
    await flushPromises();

    expect(wrapper.find('.marketplacePluginDetails__free').text()).toBe('Marketplace_Free');
  });

  it('names an open source licence as one under Free', async () => {
    mockPost.mockResolvedValue({
      ...detailsResponse,
      isPaid: false,
      versions: [{ name: '1.2.3', license: { name: 'GPL v3+' } }],
    });

    const wrapper = mountDetails({ ...cardRow, isPaid: false }, true);
    await flushPromises();

    expect(wrapper.find('.marketplacePluginDetails__freeLicense').text())
      .toBe('Marketplace_OpenSourceLicense');
  });

  it('shows any other licence under Free by its name alone', async () => {
    mockPost.mockResolvedValue({
      ...detailsResponse,
      isPaid: false,
      versions: [{ name: '1.2.3', license: { name: 'Commercial license' } }],
    });

    const wrapper = mountDetails({ ...cardRow, isPaid: false }, true);
    await flushPromises();

    expect(wrapper.find('.marketplacePluginDetails__freeLicense').text())
      .toBe('Commercial license');
  });

  it('falls back to a generic message when the failure carries none', async () => {
    mockPost.mockRejectedValue({});

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();
    await flushPromises();

    expect(vmOf(wrapper).fetchErrorMessage).toBe('General_ErrorRequest');
  });

  it('aborts a request still in flight when another plugin is opened', async () => {
    mockPost.mockReturnValue(new Promise(() => { /* never settles */ }));

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();

    const firstController = mockPost.mock.calls[0][2].abortController;
    const abortSpy = vi.spyOn(firstController, 'abort');

    await wrapper.setProps({ pluginCard: { ...cardRow, name: 'PaidPlugin2' } });

    expect(abortSpy).toHaveBeenCalled();
    expect(mockPost).toHaveBeenCalledTimes(2);
    expect(mockPost.mock.calls[1][1]).toEqual({ pluginName: 'PaidPlugin2' });
  });

  it('does not carry one plugin\'s details into the next when the new request fails', async () => {
    mockPost.mockResolvedValueOnce(detailsResponse);

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();
    await flushPromises();
    expect((vmOf(wrapper).plugin.shop as { url: string }).url).toBe('https://shop.example');

    mockPost.mockRejectedValueOnce({ message: 'nope' });
    await wrapper.setProps({ pluginCard: { ...cardRow, name: 'PaidPlugin2', displayName: 'Two' } });
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

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();
    await wrapper.setProps({ pluginCard: { ...cardRow, name: 'PaidPlugin2', displayName: 'Two' } });

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

    const wrapper = mountDetails(cardRow);
    await wrapper.vm.$nextTick();
    await wrapper.setProps({ pluginCard: { ...cardRow, name: 'PaidPlugin2' } });

    // the first request rejects only after being superseded, so it must not clear the spinner
    // or report an error belonging to a plugin the user is no longer looking at
    rejectFirst({ message: 'stale failure' });
    await flushPromises();

    expect(vmOf(wrapper).isLoading).toBe(true);
    expect(vmOf(wrapper).fetchErrorMessage).toBe('');
  });
});
