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
}));

// Field is replaced so the settings screen can be mounted without the whole form stack, but the
// helpers deciding whether the control is locked are the real ones, taken from their source rather
// than the plugin entry point (which would pull in every component again)
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
}));

import ExcludeQueryParameterSettings from './ExcludeQueryParameterSettings.vue';

const exactControl = { cnil: { policyTitle: 'CNIL', scope: 'instance', constraintType: 'exact' } };
const boundedControl = { cnil: { policyTitle: 'CNIL', scope: 'instance', constraintType: 'min' } };

function mountSettings(exclusionTypePolicyControlled = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(ExcludeQueryParameterSettings as any, {
    props: {
      exclusionTypeForQueryParams: 'common_session_parameters',
      excludedQueryParametersGlobal: [],
      commonSensitiveQueryParams: [],
      exclusionTypePolicyControlled,
    },
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        $sanitize: (html: string) => html,
      },
    },
  });
}

function exclusionTypeField(wrapper: ReturnType<typeof mountSettings>) {
  const field = wrapper.findAllComponents({ name: 'FieldStub' })
    .find((candidate) => candidate.props('name') === 'exclusionType');

  if (!field) {
    throw new Error('the exclusion type field was not rendered');
  }

  return field;
}

describe('SitesManager/ExcludeQueryParameterSettings', () => {
  it('leaves the exclusion type editable and unannotated while no policy is enforced', () => {
    const field = exclusionTypeField(mountSettings());

    expect(field.props('disabled')).toBe(false);
    expect(field.props('extraMetadata')).toBeUndefined();
  });

  it('locks the exclusion type and annotates it when a policy leaves no alternative', () => {
    const field = exclusionTypeField(mountSettings(exactControl));

    expect(field.props('disabled')).toBe(true);
    expect(field.props('extraMetadata')).toEqual({
      compliancePolicyControlled: exactControl,
    });
  });

  it('keeps the exclusion type editable when a policy only bounds it, but still annotates it', () => {
    const field = exclusionTypeField(mountSettings(boundedControl));

    expect(field.props('disabled')).toBe(false);
    expect(field.props('extraMetadata')).toEqual({
      compliancePolicyControlled: boundedControl,
    });
  });
});
