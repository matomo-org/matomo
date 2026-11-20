/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import GranularComplianceTable from './GranularComplianceTable.vue';
import { PolicySetting } from './GranularCompliance.store';

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

function createWrapper(props = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(GranularComplianceTable as any, {
    props: {
      settings: [setting()],
      ...props,
    },
    global: {
      mocks: {
        translate: (key: string) => key,
        $sanitize: (html: string) => html,
      },
    },
  });
}

describe('PrivacyManager/GranularComplianceTable', () => {
  it('renders one row per setting with name, texts and status', () => {
    const wrapper = createWrapper({
      settings: [
        setting({ status: 'compliant' }),
        setting({ id: 'B.Two', name: 'Second', status: 'enforced', enforced: true }),
      ],
      localEnforced: { 'PrivacyManager.IPAnonymisation': false, 'B.Two': true },
    });

    const rows = wrapper.findAll('tbody tr');
    expect(rows).toHaveLength(2);
    expect(rows[0].find('.settingName').text()).toBe('IP Anonymisation Enabled');
    expect(rows[0].find('.status').text()).toContain('PrivacyManager_ComplianceCompliant');
    expect(rows[0].find('.status .icon-ok').exists()).toBe(true);
    expect(rows[1].find('.status').text()).toContain('PrivacyManager_ComplianceStatusCompliantEnforced');
    expect(rows[1].find('input[type=checkbox]').element as HTMLInputElement).toHaveProperty('checked', true);
  });

  it('renders non-compliant status without an icon', () => {
    const wrapper = createWrapper({ settings: [setting({ status: 'non_compliant' })] });

    const status = wrapper.find('.status');
    expect(status.text()).toContain('PrivacyManager_ComplianceNonCompliant');
    expect(status.find('.icon').exists()).toBe(false);
  });

  it('overlays applies-on-save for dirty toggleable settings', () => {
    const wrapper = createWrapper({
      settings: [setting({ status: 'non_compliant' })],
      localEnforced: { 'PrivacyManager.IPAnonymisation': true },
      dirtySettingIds: ['PrivacyManager.IPAnonymisation'],
    });

    const status = wrapper.find('.status');
    expect(status.classes()).toContain('applies-on-save');
    expect(status.text()).toContain('PrivacyManager_ComplianceStatusAppliesOnSave');
    expect(status.find('.icon-warning').exists()).toBe(true);
  });

  it('gives each toggle an accessible name', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('input[type=checkbox]').attributes('aria-label'))
      .toBe('PrivacyManager_ComplianceStatusEnforced: IP Anonymisation Enabled');
  });

  it('emits toggle with the setting id when the switch changes', async () => {
    const wrapper = createWrapper();

    await wrapper.find('input[type=checkbox]').trigger('change');

    expect(wrapper.emitted('toggle')).toEqual([['PrivacyManager.IPAnonymisation']]);
  });

  it('disables switches when the table is disabled', () => {
    const wrapper = createWrapper({ disabled: true });

    expect((wrapper.find('input[type=checkbox]').element as HTMLInputElement).disabled).toBe(true);
  });

  it('renders lock statuses and no switch for non-toggleable settings', () => {
    const wrapper = createWrapper({
      settings: [setting({
        id: 'Core.ThirdPartyCookies',
        toggleable: false,
        enforced: null,
        status: 'on_by_default',
        section: 'external',
      })],
    });

    expect(wrapper.find('.status').text()).toContain('PrivacyManager_ComplianceStatusOnByDefault');
    expect(wrapper.find('.status .icon-locked').exists()).toBe(true);
    expect(wrapper.find('input[type=checkbox]').exists()).toBe(false);
  });

  it('hides the whole toggle column when showToggles is off', () => {
    const wrapper = createWrapper({ showToggles: false });

    expect(wrapper.findAll('thead th')).toHaveLength(4);
    expect(wrapper.find('.toggleColumn').exists()).toBe(false);
  });
});
