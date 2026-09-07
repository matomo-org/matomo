/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
  externalLink: (url: string) => url,
  MatomoUrl: { stringify: () => '', urlParsed: { value: {} } },
  NotificationsStore: { show: () => '', scrollToNotification: () => {} },
  AjaxHelper: { post: () => Promise.resolve({}) },
}));

// Field is replaced so the screen can be mounted without the whole form stack, but the helper
// deciding whether a control is locked is the real one, taken from its source rather than the
// plugin entry point (which would pull in every component again)
vi.mock('CorePluginsAdmin', async () => ({
  ...(await import('../../../../CorePluginsAdmin/vue/src/FormField/compliancePolicy')),
  Field: defineComponent({
    name: 'FieldStub',
    props: {
      name: { type: String, default: '' },
      disabled: { type: Boolean, default: false },
      extraMetadata: { type: Object, default: undefined },
    },
    template: '<div class="field" />',
  }),
  PasswordConfirmation: defineComponent({ template: '<div><slot/></div>' }),
  SaveButton: defineComponent({ template: '<button/>' }),
  Form: {},
}));

import AnonymizeIp from './AnonymizeIp.vue';

function policyControlled(constraintType: 'exact'|'min'|'max') {
  return {
    compliancePolicyControlled: {
      cnil: { policyTitle: 'CNIL', scope: 'instance', constraintType },
    },
  };
}

function mountAnonymizeIp(extraMetadata: Record<string, unknown> = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(AnonymizeIp as any, {
    props: {
      ipAnonymizerEnabled: true,
      ipAddressMaskLength: 2,
      anonymizeOrderId: true,
      anonymizeReferrer: 'exclude_path',
      maskLengthOptions: [{ key: 2, value: '2 bytes' }],
      useAnonymizedIpForVisitEnrichmentOptions: [{ key: 0, value: 'no' }],
      referrerAnonymizationOptions: {},
      trackerFileName: 'matomo.js',
      trackerWritable: true,
      // instance wide settings, which is what keeps the fields on screen and their names unsuffixed
      idSiteSpecific: '',
      extraMetadata,
    },
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        $sanitize: (html: string) => html,
      },
    },
  });
}

// the control each policy-controlled setting is rendered as, whose name differs from the name the
// setting is known by on the server
const FIELD_NAMES: Record<string, string> = {
  ipAnonymizerEnabled: 'anonymizeIpSettings',
  ipAddressMaskLength: 'maskLength',
  anonymizeOrderId: 'anonymizeOrderId',
  anonymizeReferrer: 'anonymizeReferrer',
};

function field(wrapper: ReturnType<typeof mountAnonymizeIp>, setting: string) {
  const found = wrapper.findAllComponents({ name: 'FieldStub' })
    .find((candidate) => candidate.props('name') === FIELD_NAMES[setting]);

  if (!found) {
    throw new Error(`the ${setting} field was not rendered`);
  }

  return found;
}

describe('PrivacyManager/AnonymizeIp', () => {
  it.each(['ipAnonymizerEnabled', 'ipAddressMaskLength', 'anonymizeOrderId', 'anonymizeReferrer'])(
    'leaves %s editable while no policy is enforced',
    (name) => {
      expect(field(mountAnonymizeIp(), name).props('disabled')).toBe(false);
    },
  );

  it.each(['ipAnonymizerEnabled', 'ipAddressMaskLength', 'anonymizeOrderId', 'anonymizeReferrer'])(
    'locks %s when a policy leaves it no compliant alternative',
    (name) => {
      const wrapper = mountAnonymizeIp({ [name]: policyControlled('exact') });

      expect(field(wrapper, name).props('disabled')).toBe(true);
    },
  );

  it.each(['ipAddressMaskLength', 'anonymizeReferrer'])(
    'keeps %s editable when a policy only bounds it, so a stricter value stays selectable',
    (name) => {
      const wrapper = mountAnonymizeIp({ [name]: policyControlled('min') });

      expect(field(wrapper, name).props('disabled')).toBe(false);
    },
  );

  it('locks only the field the policy controls', () => {
    const wrapper = mountAnonymizeIp({ anonymizeOrderId: policyControlled('exact') });

    expect(field(wrapper, 'anonymizeOrderId').props('disabled')).toBe(true);
    expect(field(wrapper, 'ipAnonymizerEnabled').props('disabled')).toBe(false);
    expect(field(wrapper, 'ipAddressMaskLength').props('disabled')).toBe(false);
    expect(field(wrapper, 'anonymizeReferrer').props('disabled')).toBe(false);
  });

  it('passes the controlling policies on to the field, so it can explain itself', () => {
    const wrapper = mountAnonymizeIp({ ipAnonymizerEnabled: policyControlled('exact') });

    expect(field(wrapper, 'ipAnonymizerEnabled').props('extraMetadata'))
      .toEqual(policyControlled('exact'));
  });
});
