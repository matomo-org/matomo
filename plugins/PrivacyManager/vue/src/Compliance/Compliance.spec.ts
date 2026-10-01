/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// 'CoreHome' and 'CorePluginsAdmin' are webpack externals rather than real modules, so the
// mocks have to be virtual for Jest's resolver to accept them.
jest.mock('CoreHome', () => ({
  ActivityIndicator: { template: '<div />' },
  AjaxHelper: { fetch: jest.fn(), post: jest.fn() },
  ContentBlock: { template: '<div />' },
  EnrichedHeadline: { template: '<div><slot /></div>' },
  Matomo: { siteName: 'Site 2', helper: { htmlDecode: (value: string) => value } },
  MatomoUrl: { urlParsed: { value: {} } },
  SiteSelector: {
    name: 'SiteSelector',
    props: ['modelValue', 'placeholder', 'showAllSitesItem'],
    emits: ['update:modelValue'],
    template: '<div />',
  },
  translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
}), { virtual: true });

jest.mock('CorePluginsAdmin', () => ({
  PasswordConfirmation: { template: '<div />' },
  SaveButton: { template: '<div />' },
  Field: {
    name: 'Field',
    props: ['modelValue', 'options', 'title', 'name', 'uicontrol'],
    emits: ['update:modelValue'],
    template: '<div />',
  },
}), { virtual: true });

jest.mock('./Compliance.store', () => ({
  fetchCompliancePolicies: jest.fn().mockResolvedValue([
    { id: 'cnil_v1', title: 'CNIL', description: 'CNIL description' },
  ]),
}));

import { mount, flushPromises } from '@vue/test-utils';
import { MatomoUrl } from 'CoreHome';
import Compliance from './Compliance.vue';
import ComplianceOverview from './ComplianceOverview.vue';
import GranularComplianceOverview from './GranularComplianceOverview.vue';

async function createWrapper(urlParams = {}, props = {}) {
  (MatomoUrl.urlParsed as { value: Record<string, unknown> }).value = urlParams;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(Compliance as any, {
    props,
    shallow: true,
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        $sanitize: (html: string) => html,
      },
    },
  });
  await flushPromises();

  return wrapper;
}

function scopeField(wrapper: ReturnType<typeof mount>) {
  return wrapper.findComponent({ name: 'Field' });
}

describe('PrivacyManager/Compliance', () => {
  it('configures every website when no scope is requested', async () => {
    const wrapper = await createWrapper({ idSite: '1' });

    expect(scopeField(wrapper).props('modelValue')).toEqual('all');
    expect(wrapper.findComponent({ name: 'SiteSelector' }).exists()).toBe(false);
    expect(wrapper.findComponent(ComplianceOverview).props('idSite')).toEqual('all');
    expect(wrapper.find('.complianceScopeNotice').text())
      .toEqual('PrivacyManager_ComplianceScopeAllWebsitesNotice');
  });

  it('offers both scopes and always explains the override behaviour', async () => {
    const wrapper = await createWrapper({ idSite: 'all' });

    expect(scopeField(wrapper).props('options')).toEqual([
      { key: 'all', value: 'General_MultiSitesSummary' },
      { key: 'site', value: 'PrivacyManager_ComplianceScopeSingleWebsite' },
    ]);
    expect(wrapper.find('.complianceScopeOverrideNote').text())
      .toEqual('PrivacyManager_ComplianceScopeOverrideNote');
  });

  it('configures the requested single website', async () => {
    const wrapper = await createWrapper({ idSite: '2', complianceScope: 'site' });

    expect(scopeField(wrapper).props('modelValue')).toEqual('site');
    expect(wrapper.findComponent({ name: 'SiteSelector' }).exists()).toBe(true);
    expect(wrapper.findComponent(ComplianceOverview).props('idSite')).toEqual('2');
    expect(wrapper.find('.complianceScopeNotice').text())
      .toEqual('PrivacyManager_ComplianceScopeSingleWebsiteNotice|Site 2');
  });

  it.each([
    { idSite: 'all', complianceScope: 'site' },
    { complianceScope: 'site' },
    {},
  ])('configures every website unless one is named (%o)', async (urlParams) => {
    const wrapper = await createWrapper(urlParams);

    expect(scopeField(wrapper).props('modelValue')).toEqual('all');
    expect(wrapper.findComponent({ name: 'SiteSelector' }).exists()).toBe(false);
    expect(wrapper.findComponent(ComplianceOverview).props('idSite')).toEqual('all');
  });

  it('waits for a website to be picked before configuring a single website', async () => {
    const wrapper = await createWrapper({ idSite: '1' });

    await scopeField(wrapper).vm.$emit('update:modelValue', 'site');
    await flushPromises();

    expect(wrapper.findComponent({ name: 'SiteSelector' }).exists()).toBe(true);
    expect(wrapper.findComponent(ComplianceOverview).exists()).toBe(false);
    expect(wrapper.find('.complianceScopeNotice').exists()).toBe(false);

    await wrapper.findComponent({ name: 'SiteSelector' }).vm.$emit('update:modelValue', { id: 3, name: 'Site 3' });
    await flushPromises();

    expect(wrapper.findComponent(ComplianceOverview).props('idSite')).toEqual('3');
    expect(wrapper.find('.complianceScopeNotice').text())
      .toEqual('PrivacyManager_ComplianceScopeSingleWebsiteNotice|Site 3');
  });

  it('never offers All Websites inside the website selector', async () => {
    const wrapper = await createWrapper({ idSite: '2', complianceScope: 'site' });

    expect(wrapper.findComponent({ name: 'SiteSelector' }).props('showAllSitesItem')).toBe(false);
  });

  it('shows the granular overview when the granular dashboard is enabled', async () => {
    const wrapper = await createWrapper({ idSite: 'all' }, { granularComplianceEnabled: true });

    expect(wrapper.findComponent(ComplianceOverview).exists()).toBe(false);
    expect(wrapper.findComponent(GranularComplianceOverview).props('idSite')).toEqual('all');
  });
});
