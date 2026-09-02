/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

jest.mock('CoreHome', () => {
  // FormField pulls in every field component, and they import from CoreHome as well, so the mock
  // has to cover what they use and not only what FormField itself needs. The factory is hoisted,
  // so everything it refers to has to be defined inside it, and CoreHome has no jest module
  // mapping, so it is mocked virtually.
  const stubComponent = { template: '<div/>' };
  const stubDirective = {};

  return {
    // the note is asserted through the key and the policy title it is given, so that the test
    // fails when either the wrong note or the wrong policy is shown
    translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
    MatomoUrl: {
      stringify: (params: Record<string, unknown>) => new URLSearchParams(
        params as Record<string, string>,
      ).toString(),
      urlParsed: { value: {} },
    },
    // only the slot matters here; context and noclear fall through as attributes
    Notification: { template: '<div class="notification"><slot/></div>' },
    useExternalPluginComponent: () => stubComponent,
    Matomo: { helper: { normalize: (value: string) => value.toLowerCase() } },
    debounce: (callback: (...args: unknown[]) => unknown) => callback,
    FocusAnywhereButHere: stubDirective,
    FocusIf: stubDirective,
    FieldArray: stubComponent,
    MultiPairField: stubComponent,
    PasswordRule: stubComponent,
    PasswordStrength: stubComponent,
    SiteRef: stubComponent,
    SiteSelector: stubComponent,
  };
}, { virtual: true });

import FormField from './FormField.vue';
import { CompliancePolicyControl } from './compliancePolicy';

// the concrete field components reach for these on mount; the note under test does not depend on
// them, so they only have to exist
beforeAll(() => {
  (window as unknown as Record<string, unknown>).Materialize = { updateTextFields: () => {} };
  (window as unknown as Record<string, unknown>).vueSanitize = (value: string) => value;
});

function control(overrides: Partial<CompliancePolicyControl> = {}): CompliancePolicyControl {
  return {
    policyTitle: 'CNIL',
    scope: 'instance',
    constraintType: 'exact',
    ...overrides,
  };
}

function mountField(compliancePolicyControlled?: Record<string, CompliancePolicyControl>) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(FormField as any, {
    props: {
      modelValue: 'a value',
      formField: {
        name: 'a_setting',
        title: 'A setting',
        uiControl: 'text',
        ...(compliancePolicyControlled
          ? { extraMetadata: { compliancePolicyControlled, idSite: 1 } }
          : {}),
      },
    },
    global: {
      mocks: {
        // the templates call translate() as a global, separately from the import the note uses
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        $sanitize: (html: string) => html,
      },
    },
  });
}

describe('FormField compliance policy note', () => {
  it('shows no note for a field no policy controls', () => {
    expect(mountField().find('.notification').exists()).toBe(false);
  });

  it.each([
    ['config', 'PrivacyManager_PolicyControlledSettingLockedConfig'],
    ['instance', 'PrivacyManager_PolicyControlledSettingLockedInstance'],
    ['site', 'PrivacyManager_PolicyControlledSettingLockedWebsite'],
  ])('names the %s scope in the note of a locked field', (scope, expectedKey) => {
    const wrapper = mountField({
      cnil: control({ scope: scope as CompliancePolicyControl['scope'], constraintType: 'exact' }),
    });

    expect(wrapper.find('.notification').text()).toContain(`${expectedKey}|CNIL`);
  });

  it.each([
    ['config', 'PrivacyManager_PolicyControlledSettingConstrainedConfig'],
    ['instance', 'PrivacyManager_PolicyControlledSettingConstrainedInstance'],
    ['site', 'PrivacyManager_PolicyControlledSettingConstrainedWebsite'],
  ])('names the %s scope in the note of a bounded field', (scope, expectedKey) => {
    const wrapper = mountField({
      cnil: control({ scope: scope as CompliancePolicyControl['scope'], constraintType: 'min' }),
    });

    expect(wrapper.find('.notification').text()).toContain(`${expectedKey}|CNIL`);
  });

  it('describes the field as locked when any one policy leaves no alternative', () => {
    const wrapper = mountField({
      bounded: control({ constraintType: 'min' }),
      exact: control({ constraintType: 'exact' }),
    });

    expect(wrapper.find('.notification').text())
      .toContain('PrivacyManager_PolicyControlledSettingLockedInstance');
  });

  it('reports the furthest reaching scope when policies disagree', () => {
    // mirrors CompliancePolicy::getEnforcementScope(): a value pinned in the config file cannot
    // be changed from the dashboard, and an instance wide policy already covers every website
    const wrapper = mountField({
      site: control({ scope: 'site' }),
      config: control({ scope: 'config' }),
    });

    expect(wrapper.find('.notification').text())
      .toContain('PrivacyManager_PolicyControlledSettingLockedConfig');
  });

  it('links to the compliance dashboard for enforcement that can be changed there', () => {
    const wrapper = mountField({ cnil: control({ scope: 'instance' }) });
    const link = wrapper.find('.notification a');

    expect(link.exists()).toBe(true);
    expect(link.attributes('href')).toContain('module=PrivacyManager');
    expect(link.attributes('href')).toContain('action=compliance');
  });

  it('offers no dashboard link when the value is pinned in the config file', () => {
    const wrapper = mountField({ cnil: control({ scope: 'config' }) });

    expect(wrapper.find('.notification').exists()).toBe(true);
    expect(wrapper.find('.notification a').exists()).toBe(false);
  });
});
