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
  AjaxHelper: { fetch: () => Promise.resolve({}), post: () => Promise.resolve({}) },
  NotificationsStore: { show: () => '', scrollToNotification: () => {} },
}));

// Field is replaced so the screen can be mounted without the whole form stack, but the helper
// building the annotation is the real one, taken from its source rather than the plugin entry
// point (which would pull in every component again)
vi.mock('CorePluginsAdmin', async () => ({
  ...(await import('../../../../CorePluginsAdmin/vue/src/FormField/compliancePolicy')),
  Field: defineComponent({
    name: 'FieldStub',
    props: {
      name: { type: String, default: '' },
      modelValue: { type: [String, Number], default: '' },
      extraMetadata: { type: Object, default: undefined },
    },
    template: '<div class="field" />',
  }),
  PasswordConfirmation: defineComponent({ template: '<div><slot/></div>' }),
  SaveButton: defineComponent({ template: '<button/>' }),
  Form: {},
}));

import DeleteOldLogs from './DeleteOldLogs.vue';

const cnilControls = {
  cnil: { policyTitle: 'CNIL', scope: 'instance', constraintType: 'max' },
};

function mountDeleteOldLogs(compliancePolicyControlled?: Record<string, unknown>) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(DeleteOldLogs as any, {
    props: {
      isDataPurgeSettingsEnabled: true,
      deleteData: {
        config: {
          delete_logs_enable: 1,
          delete_logs_older_than: 180,
          delete_logs_schedule_lowest_interval: 7,
        },
        ...(compliancePolicyControlled ? { compliancePolicyControlled } : {}),
      },
      scheduleDeletionOptions: {},
    },
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        externalRawLink: (url: string) => url,
        $sanitize: (html: string) => html,
      },
    },
  });
}

function retentionField(wrapper: ReturnType<typeof mountDeleteOldLogs>) {
  const found = wrapper.findAllComponents({ name: 'FieldStub' })
    .find((candidate) => candidate.props('name') === 'deleteOlderThan');

  if (!found) {
    throw new Error('the log retention field was not rendered');
  }

  return found;
}

describe('PrivacyManager/DeleteOldLogs', () => {
  it('leaves the retention unannotated while no policy caps it', () => {
    expect(retentionField(mountDeleteOldLogs()).props('extraMetadata')).toBeUndefined();
  });

  it('leaves the retention unannotated when no policy is reported for it', () => {
    expect(retentionField(mountDeleteOldLogs({})).props('extraMetadata')).toBeUndefined();
  });

  it('annotates the retention with the policies capping it', () => {
    expect(retentionField(mountDeleteOldLogs(cnilControls)).props('extraMetadata')).toEqual({
      compliancePolicyControlled: cnilControls,
    });
  });
});
