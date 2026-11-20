/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent } from 'vue';

vi.mock('CoreHome', () => ({
  ActivityIndicator: { template: '<div />' },
  AjaxHelper: { fetch: vi.fn(), post: vi.fn() },
  ContentBlock: { template: '<div><slot /></div>' },
  translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
}));

vi.mock('CorePluginsAdmin', () => ({
  PasswordConfirmation: defineComponent({
    name: 'PasswordConfirmationStub',
    props: { requireDeleteConfirmation: { type: Boolean, default: false } },
    template: '<div><slot /></div>',
  }),
  SaveButton: { template: '<div />' },
}));

import { mount, flushPromises } from '@vue/test-utils';
import { AjaxHelper } from 'CoreHome';
import GranularComplianceOverview from './GranularComplianceOverview.vue';

const RAW_DATA_RETENTION = 'PrivacyManager.ReportRetention';

function setting(id: string, enforced: boolean) {
  return {
    id,
    name: id,
    whatItDoes: '',
    impact: '',
    status: 'notApplied',
    enforced,
    toggleable: true,
    section: 'settings',
  };
}

async function mountOverview(...settings: ReturnType<typeof setting>[]) {
  (AjaxHelper.fetch as ReturnType<typeof vi.fn>).mockResolvedValue({
    policy: 'cnil_v1',
    title: 'CNIL',
    description: '',
    configControlled: false,
    policyEnforced: false,
    settings,
  });

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(GranularComplianceOverview as any, {
    props: { idSite: '1', complianceType: 'cnil_v1', title: 'CNIL' },
    global: {
      // the table renders one row per setting and is covered by its own spec
      stubs: { GranularComplianceTable: true },
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        $sanitize: (html: string) => html,
      },
    },
  });

  await flushPromises();

  return wrapper;
}

function requiresTypedDeletion(wrapper: Awaited<ReturnType<typeof mountOverview>>) {
  return wrapper.findComponent({ name: 'PasswordConfirmationStub' })
    .props('requireDeleteConfirmation');
}

describe('PrivacyManager/GranularComplianceOverview', () => {
  it('asks for a password alone while raw data retention stays as it is', async () => {
    const wrapper = await mountOverview(setting(RAW_DATA_RETENTION, false));

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });

  // the save posts the changed settings only, so leaving retention as it was schedules no
  // deletion and the word would be asked for nothing
  it('asks for a password alone while raw data retention stays enforced', async () => {
    const wrapper = await mountOverview(setting(RAW_DATA_RETENTION, true));

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });

  it('asks for the deletion to be typed out once raw data retention is switched on', async () => {
    const wrapper = await mountOverview(setting(RAW_DATA_RETENTION, false));

    wrapper.vm.toggleSetting(RAW_DATA_RETENTION);
    await wrapper.vm.$nextTick();

    expect(requiresTypedDeletion(wrapper)).toBe(true);
  });

  it('asks for a password alone when another setting is switched on', async () => {
    const wrapper = await mountOverview(
      setting(RAW_DATA_RETENTION, false),
      setting('DevicesDetection.DeviceModelDetectionDisabled', false),
    );

    wrapper.vm.toggleSetting('DevicesDetection.DeviceModelDetectionDisabled');
    await wrapper.vm.$nextTick();

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });

  it('asks for a password alone when another setting changes with retention on', async () => {
    const wrapper = await mountOverview(
      setting(RAW_DATA_RETENTION, true),
      setting('DevicesDetection.DeviceModelDetectionDisabled', false),
    );

    wrapper.vm.toggleSetting('DevicesDetection.DeviceModelDetectionDisabled');
    await wrapper.vm.$nextTick();

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });
});
