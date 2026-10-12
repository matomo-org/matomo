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
  Field: defineComponent({
    name: 'FieldStub',
    props: { modelValue: { type: Boolean, default: false } },
    emits: ['update:modelValue'],
    template: '<div class="field" />',
  }),
  PasswordConfirmation: defineComponent({
    name: 'PasswordConfirmationStub',
    props: { requireDeleteConfirmation: { type: Boolean, default: false } },
    template: '<div><slot /></div>',
  }),
  SaveButton: { template: '<div />' },
}));

import { mount, flushPromises } from '@vue/test-utils';
import { AjaxHelper } from 'CoreHome';
import ComplianceOverview from './ComplianceOverview.vue';

async function mountOverview(complianceModeEnforced: boolean) {
  (AjaxHelper.fetch as ReturnType<typeof vi.fn>).mockResolvedValue({
    complianceModeEnforced,
    complianceConfigControlled: false,
    complianceRequirements: [],
  });

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(ComplianceOverview as any, {
    props: {
      idSite: '1',
      complianceType: 'cnil_v1',
      title: 'CNIL',
      description: 'CNIL description',
    },
    global: {
      // the requirements table is covered by its own spec
      stubs: { ComplianceTable: true },
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        $sanitize: (html: string) => html,
      },
    },
  });

  await flushPromises();

  return wrapper;
}

type Overview = Awaited<ReturnType<typeof mountOverview>>;

async function setCheckbox(wrapper: Overview, enforce: boolean) {
  wrapper.findComponent({ name: 'FieldStub' }).vm.$emit('update:modelValue', enforce);
  await wrapper.vm.$nextTick();
}

function requiresTypedDeletion(wrapper: Overview) {
  return wrapper.findComponent({ name: 'PasswordConfirmationStub' })
    .props('requireDeleteConfirmation');
}

describe('PrivacyManager/ComplianceOverview', () => {
  it('asks for a password alone while compliance is not enforced', async () => {
    const wrapper = await mountOverview(false);

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });

  // the checkbox loads ticked in this case, so a save that changes nothing re-enforces what
  // is already enforced and schedules no deletion
  it('asks for a password alone while compliance stays enforced', async () => {
    const wrapper = await mountOverview(true);

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });

  it('asks for the deletion to be typed out once compliance is switched on', async () => {
    const wrapper = await mountOverview(false);

    await setCheckbox(wrapper, true);

    expect(requiresTypedDeletion(wrapper)).toBe(true);
  });

  it('warns about the deletion it is asking to be typed out', async () => {
    const wrapper = await mountOverview(false);
    expect(wrapper.find('h2').exists()).toBe(false);

    await setCheckbox(wrapper, true);

    const dialog = wrapper.findComponent({ name: 'PasswordConfirmationStub' });
    expect(dialog.find('h2').text())
      .toBe('PrivacyManager_ComplianceEnforceRetentionConfirmTitle');
    expect(dialog.find('p').text())
      .toBe('PrivacyManager_ComplianceEnforceRetentionConfirmBody');
  });

  it('asks for a password alone when enforcement is switched off', async () => {
    const wrapper = await mountOverview(true);

    await setCheckbox(wrapper, false);

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });

  it('asks for a password alone when the checkbox ends up where it started', async () => {
    const wrapper = await mountOverview(true);

    await setCheckbox(wrapper, false);
    await setCheckbox(wrapper, true);

    expect(requiresTypedDeletion(wrapper)).toBe(false);
  });
});
