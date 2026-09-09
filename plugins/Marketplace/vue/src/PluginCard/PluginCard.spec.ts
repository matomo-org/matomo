/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, VueWrapper } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  MatomoUrl: {
    urlParsed: { value: {} },
    parsed: { value: { idSite: '1' } },
    hashParsed: { value: {} },
    stringify: (params: Record<string, unknown>) => new URLSearchParams(
      Object.entries(params).map(([k, v]) => [k, String(v)]),
    ).toString(),
  },
  translate: (key: string, ...args: string[]) => (args.length ? `${key}:${args.join(',')}` : key),
  translateOrDefault: (key: string) => key,
  ucfirst: (value: string) => `${value.charAt(0).toUpperCase()}${value.slice(1)}`,
}));

/* eslint-disable import/first */
import PluginCard from './PluginCard.vue';
import { PluginCard as PluginCardType } from '../types';

function makePlugin(overrides: Partial<PluginCardType> = {}): PluginCardType {
  return {
    name: 'Funnels',
    displayName: 'Funnels',
    description: 'Understand where visitors drop off',
    owner: 'InnoCraft',
    categories: ['insights'],
    coverImage: 'https://plugins.matomo.org/img/funnels.png',
    isFree: true,
    isPaid: false,
    isTheme: false,
    isInstalled: false,
    isActivated: false,
    isInvalid: false,
    isDownloadable: true,
    canBeUpdated: false,
    hasDownloadLink: true,
    hasExceededLicense: false,
    isMissingLicense: false,
    isEligibleForFreeTrial: false,
    isTrialRequested: false,
    canTrialBeRequested: false,
    missingRequirements: [],
    numDownloads: 100,
    numDownloadsPretty: '100',
    lastUpdated: 'Jun 8, 2026',
    lastUpdatedRaw: '2026-06-08 00:00:00',
    createdDateTime: '2020-01-01 00:00:00',
    consumer: {},
    licenseStatus: '',
    ...overrides,
  } as unknown as PluginCardType;
}

function mountCard(plugin: Partial<PluginCardType> = {}, props: Record<string, unknown> = {}) {
  return mount(PluginCard, {
    props: {
      plugin: makePlugin(plugin),
      isSuperUser: true,
      isPluginsAdminEnabled: true,
      isMultiServerEnvironment: false,
      isValidConsumer: true,
      isAutoUpdatePossible: true,
      activateNonce: 'a',
      deactivateNonce: 'd',
      installNonce: 'i',
      updateNonce: 'u',
      ...props,
    },
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => (
          args.length ? `${key}:${args.join(',')}` : key
        ),
        externalRawLink: (url: string) => url,
      },
    },
  });
}

const actions = (wrapper: VueWrapper) => wrapper.find('.pluginCard__actions');

describe('Marketplace/PluginCard', () => {
  describe('presentation', () => {
    it('renders the display name and description', () => {
      const wrapper = mountCard();
      expect(wrapper.find('.pluginCard__title').text()).toBe('Funnels');
      expect(wrapper.find('.pluginCard__description').text())
        .toBe('Understand where visitors drop off');
    });

    it(
      'carries a data-plugin attribute so a deep link can scroll to it without a name selector',
      () => {
        expect(mountCard().find('.pluginCard').attributes('data-plugin')).toBe('Funnels');
      },
    );

    it('shows the By Matomo chip only for a Matomo-owned plugin', () => {
      expect(mountCard({ owner: 'InnoCraft' }).find('.pluginCard__chip--matomo').exists())
        .toBe(false);
      expect(mountCard({ owner: 'piwik' }).find('.pluginCard__chip--matomo').exists()).toBe(true);
      expect(mountCard({ owner: 'matomo-org' }).find('.pluginCard__chip--matomo').exists())
        .toBe(true);
    });

    it('credits Matomo with the flat mark, which takes a colour, not the four colour PNG', () => {
      const wrapper = mountCard({ owner: 'piwik' });
      expect(wrapper.find('.pluginCard__badge svg').exists()).toBe(true);
      expect(wrapper.find('img[src*="matomo-badge"]').exists()).toBe(false);
    });

    it('shows a category chip, but never for an unclassified plugin', () => {
      const chips = (owner: Partial<PluginCardType>) => mountCard(owner)
        .findAll('.pluginCard__chip').map((chip) => chip.text());

      expect(chips({ categories: ['insights'] })).toContain('Insights');
      expect(chips({ categories: ['uncategorised'] })).toEqual([]);
      expect(chips({ categories: [] })).toEqual([]);
    });

    it('names the first category, since the chip has room for one', () => {
      const wrapper = mountCard({ categories: ['insights', 'security'] });
      expect(wrapper.findAll('.pluginCard__chip').map((chip) => chip.text()))
        .toEqual(['Insights']);
    });

    it('labels a bundle as a bundle rather than by its category', () => {
      const wrapper = mountCard({ isBundle: true, categories: ['insights'] });
      expect(wrapper.find('.pluginCard__chip').text()).toBe('Marketplace_Bundles');
    });

    it('marks a bundle card, which PluginCard.less fills its call to action from', () => {
      expect(mountCard({ isBundle: true }).classes()).toContain('pluginCard--bundle');
      expect(mountCard({ isBundle: false }).classes()).not.toContain('pluginCard--bundle');
    });

    it('shows a bundle its seat tier where a plugin shows its update date', () => {
      const wrapper = mountCard({ isBundle: true, bundleSeats: 20 });
      expect(wrapper.find('.pluginCard__seats').text()).toBe('Marketplace_BundleUpToXUsers:20');
      expect(wrapper.find('.pluginCard__updated').exists()).toBe(false);
    });

    it('leaves the seat row off a bundle sold without a seat limit', () => {
      const wrapper = mountCard({ isBundle: true });
      expect(wrapper.find('.pluginCard__seats').exists()).toBe(false);
      expect(wrapper.find('.pluginCard__updated').exists()).toBe(true);
    });

    it('never shows a seat tier on an ordinary plugin', () => {
      const wrapper = mountCard({ isBundle: false, bundleSeats: 4 });
      expect(wrapper.find('.pluginCard__seats').exists()).toBe(false);
    });

    it('pluralises the seat tier through a key that really interpolates the count', async () => {
      const en = (await import('../../../lang/en.json')).default as
        { Marketplace: Record<string, string> };

      expect(en.Marketplace.BundleUpToXUsers).toContain('%1$s');
    });

    it('renders the localised display date, not the raw one', () => {
      const wrapper = mountCard();
      expect(wrapper.find('.pluginCard__updated').text()).toContain('Jun 8, 2026');
      expect(wrapper.find('.pluginCard__updated').text()).not.toContain('2026-06-08');
    });

    it('shows no date at all rather than an empty label when lastUpdated is missing', () => {
      expect(mountCard({ lastUpdated: '' }).find('.pluginCard__updated').exists()).toBe(false);
    });

    it('bylines a third-party plugin with its owner', () => {
      expect(mountCard({ owner: 'InnoCraft' }).find('.pluginCard__owner').text())
        .toBe('Marketplace_ByAuthor:InnoCraft');
    });

    it('drops the byline for a Matomo plugin, which the chip already credits', () => {
      const wrapper = mountCard({ owner: 'piwik' });
      expect(wrapper.find('.pluginCard__chip--matomo').exists()).toBe(true);
      expect(wrapper.find('.pluginCard__owner').exists()).toBe(false);
    });

    it('bylines through a key that really interpolates the name', async () => {
      const en = (await import('../../../lang/en.json')).default as
        { Marketplace: Record<string, string> };

      expect(en.Marketplace.ByAuthor).toContain('%1$s');
      expect(en.Marketplace.CreatedBy).not.toContain('%');
    });

    it('requests a right-sized cover and offers a retina source', () => {
      const img = mountCard().find('.pluginCard__shot img');
      expect(img.attributes('src')).toContain('?w=440&h=240');
      expect(img.attributes('srcset')).toContain('?w=880&h=480 880w');
    });

    it('drops the image but keeps the frame when the cover 404s', async () => {
      const wrapper = mountCard();
      await wrapper.find('.pluginCard__shot img').trigger('error');
      expect(wrapper.find('.pluginCard__shot img').exists()).toBe(false);
      expect(wrapper.find('.pluginCard__plate').exists()).toBe(true);
    });
  });

  describe('opening the details modal', () => {
    it(
      'emits exactly once from the title link, so the stretched link does not double-fire',
      async () => {
        const wrapper = mountCard();
        await wrapper.find('.pluginCard__titleLink').trigger('click');
        expect(wrapper.emitted('openDetails')).toHaveLength(1);
      },
    );

    it('gives the title a real deep link, so middle-click and copy-link work', () => {
      expect(mountCard().find('.pluginCard__titleLink').attributes('href'))
        .toBe('#?showPlugin=Funnels');
    });

    it('leaves a control inside the card to that control', async () => {
      const wrapper = mountCard();
      await wrapper.find('.pluginCard__actions a').trigger('click');
      expect(wrapper.emitted('openDetails')).toBeUndefined();
    });
  });

  describe('action states', () => {
    it('offers Install to a super user for an installable free plugin', () => {
      expect(actions(mountCard()).text()).toContain('Marketplace_ActionInstall');
    });

    it('offers no install link when the plugins admin is disabled', () => {
      const wrapper = mountCard({}, { isPluginsAdminEnabled: false });
      expect(actions(wrapper).text()).not.toContain('Marketplace_ActionInstall');
    });

    it('offers a free trial for a paid, trial-eligible plugin', () => {
      const wrapper = mountCard({
        isPaid: true, isFree: false, isDownloadable: false, isEligibleForFreeTrial: true,
      });
      expect(actions(wrapper).text()).toContain('Marketplace_StartFreeTrial');
    });

    it('reports an installed plugin and offers to deactivate it', () => {
      const wrapper = mountCard({ isInstalled: true, isActivated: true });
      expect(actions(wrapper).text()).toContain('General_Installed');
      expect(actions(wrapper).text()).toContain('CorePluginsAdmin_Deactivate');
    });

    it('states installed as a card row, not as the modal alert it used to be', () => {
      const wrapper = mountCard({ isInstalled: true, isActivated: true });
      expect(actions(wrapper).find('.ctaStatus--success').exists()).toBe(true);
      expect(actions(wrapper).find('.alert').exists()).toBe(false);
      expect(actions(wrapper).text()).not.toContain('(');
    });

    it('offers deactivate as a button, so it reads as the action in the row', () => {
      const wrapper = mountCard({ isInstalled: true, isActivated: true });
      const action = actions(wrapper).find('.ctaStatus__action a');
      expect(action.classes()).toContain('btn');
      expect(action.text()).toBe('CorePluginsAdmin_Deactivate');
    });

    it('offers to activate a deactivated plugin', () => {
      const wrapper = mountCard({ isInstalled: true, isActivated: false });
      expect(actions(wrapper).text()).toContain('CorePluginsAdmin_Activate');
    });

    it('suppresses activate and deactivate in a multi-server environment', () => {
      const wrapper = mountCard(
        { isInstalled: true, isActivated: true },
        { isMultiServerEnvironment: true },
      );
      expect(actions(wrapper).text()).toContain('General_Installed');
      expect(actions(wrapper).text()).not.toContain('CorePluginsAdmin_Deactivate');
    });

    it('offers an update when one is available and auto-update is possible', () => {
      const wrapper = mountCard({ isInstalled: true, canBeUpdated: true });
      expect(actions(wrapper).text()).toContain('CoreUpdater_UpdateTitle');
    });

    it('warns instead of offering an update when auto-update is impossible', () => {
      const wrapper = mountCard(
        { isInstalled: true, canBeUpdated: true },
        { isAutoUpdatePossible: false },
      );
      expect(actions(wrapper).text()).toContain('Marketplace_CannotUpdate');
      expect(actions(wrapper).text()).not.toContain('CoreUpdater_UpdateTitle');
    });

    it('states a missing license in the danger tone', () => {
      const wrapper = mountCard({ isInstalled: true, isMissingLicense: true });
      expect(actions(wrapper).find('.ctaStatus--danger').exists()).toBe(true);
      expect(actions(wrapper).text()).toContain('Marketplace_LicenseMissing');
    });

    it('states an exceeded license in the danger tone', () => {
      const wrapper = mountCard({ isInstalled: true, hasExceededLicense: true });
      expect(actions(wrapper).find('.ctaStatus--danger').exists()).toBe(true);
      expect(actions(wrapper).text()).toContain('Marketplace_LicenseExceeded');
    });

    it('leaves a failed license state something to click, and it is more details', () => {
      const wrapper = mountCard({ isInstalled: true, isMissingLicense: true });
      const action = actions(wrapper).find('.ctaStatus__action a');
      expect(action.classes()).toContain('btn');
      expect(action.text()).toBe('General_MoreDetails');
    });

    it('says a plugin cannot be installed when a requirement is missing', () => {
      const wrapper = mountCard({ missingRequirements: [{ requirement: 'php' }] });
      expect(actions(wrapper).text()).toContain('Marketplace_CannotInstall');
    });

    it('offers a non-super-user no install or activate action, only more details', () => {
      const wrapper = mountCard({}, { isSuperUser: false });
      const text = actions(wrapper).text();
      expect(text).not.toContain('Marketplace_ActionInstall');
      expect(text).not.toContain('CorePluginsAdmin_Activate');
      expect(text).toContain('General_MoreDetails');
    });

    it('lets a non-super-user request a trial, which is the one action they do get', () => {
      const wrapper = mountCard(
        { canTrialBeRequested: true },
        { isSuperUser: false },
      );
      expect(actions(wrapper).text()).toContain('Marketplace_RequestTrial');
    });

    it('tells a non-super-user a trial is already requested', () => {
      const wrapper = mountCard({ isTrialRequested: true }, { isSuperUser: false });
      expect(actions(wrapper).text()).toContain('Marketplace_TrialRequested');
    });

    it('keeps the action area present even when it renders nothing actionable, so cards in a row'
      + ' stay level', () => {
      const wrapper = mountCard({ isInstalled: true, isActivated: true }, { isSuperUser: false });
      expect(actions(wrapper).exists()).toBe(true);
    });
  });
});
