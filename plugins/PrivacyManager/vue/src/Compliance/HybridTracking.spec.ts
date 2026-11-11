/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

vi.mock('CoreHome', () => ({
  ActivityIndicator: { template: '<div />' },
  AjaxHelper: { fetch: vi.fn(), post: vi.fn() },
  ContentBlock: { template: '<div><slot /></div>' },
  externalLink: (url: string) => `<a href="${url}">`,
  translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
}));

vi.mock('CorePluginsAdmin', () => ({
  PasswordConfirmation: { name: 'PasswordConfirmationStub', template: '<div />' },
  SaveButton: { name: 'SaveButtonStub', template: '<div />' },
}));

import { mount, flushPromises } from '@vue/test-utils';
import { AjaxHelper } from 'CoreHome';
import HybridTracking from './HybridTracking.vue';

async function mountHybridTracking(idSite: string, payload = {}) {
  (AjaxHelper.fetch as ReturnType<typeof vi.fn>).mockResolvedValue({
    enabled: false,
    enabledForAllWebsites: false,
    isWritable: true,
    ...payload,
  });

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(HybridTracking as any, {
    props: { idSite },
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

function toggle(wrapper: Awaited<ReturnType<typeof mountHybridTracking>>) {
  return wrapper.find<HTMLInputElement>('#hybridTrackingEnabled');
}

describe('PrivacyManager/HybridTracking', () => {
  it('links the requirements to the setup guide', async () => {
    const wrapper = await mountHybridTracking('all');

    expect(wrapper.find('.hybridTrackingRequirements').html())
      .toContain('PrivacyManager_ComplianceHybridTrackingRequirementConsent|<a href="https://matomo.org/');
  });

  it('describes the toggle for the instance', async () => {
    const wrapper = await mountHybridTracking('all', { enabled: true, enabledForAllWebsites: true });

    expect(wrapper.text()).toContain('PrivacyManager_ComplianceHybridTrackingToggleHelpAllWebsites');
    expect(toggle(wrapper).element.checked).toBe(true);
    expect(toggle(wrapper).element.disabled).toBe(false);
    expect(wrapper.find('.hybridTrackingEnabledForAllWebsites').exists()).toBe(false);
  });

  it('describes the toggle for a website', async () => {
    const wrapper = await mountHybridTracking('1', { enabled: true });

    expect(wrapper.text()).toContain('PrivacyManager_ComplianceHybridTrackingToggleHelpSingleWebsite');
    expect(toggle(wrapper).element.checked).toBe(true);
    expect(toggle(wrapper).element.disabled).toBe(false);
  });

  it('shows a website as on, and not changeable, when it is on for every website', async () => {
    const wrapper = await mountHybridTracking('1', { enabledForAllWebsites: true });

    expect(toggle(wrapper).element.checked).toBe(true);
    expect(toggle(wrapper).element.disabled).toBe(true);
    expect(wrapper.find('.hybridTrackingEnabledForAllWebsites').text())
      .toBe('PrivacyManager_ComplianceHybridTrackingEnabledForAllWebsites');
    expect(wrapper.findComponent({ name: 'SaveButtonStub' }).exists()).toBe(false);
  });

  it('cannot be changed when the instance value is set in the config', async () => {
    const wrapper = await mountHybridTracking('all', { enabled: true, enabledForAllWebsites: true, isWritable: false });

    expect(toggle(wrapper).element.disabled).toBe(true);
    expect(wrapper.findComponent({ name: 'SaveButtonStub' }).exists()).toBe(false);
  });

  it('asks for the password before saving a change', async () => {
    const wrapper = await mountHybridTracking('1');

    await toggle(wrapper).trigger('change');
    expect(wrapper.vm.hasUnsavedChanges).toBe(true);

    await wrapper.findComponent({ name: 'SaveButtonStub' }).vm.$emit('confirm');
    expect(wrapper.findComponent({ name: 'PasswordConfirmationStub' }).attributes('model-value')).toBe('true');
  });
});
