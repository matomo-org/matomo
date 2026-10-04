/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';

// both are reached from a mock factory, which runs before anything declared here would exist
const { modalConfirm, storeState } = vi.hoisted(() => ({
  modalConfirm: vi.fn(),
  storeState: {
    value: {
      isModified: false,
      showEstimate: false,
      estimation: '',
      loadingEstimation: false,
    },
  },
}));

vi.mock('CoreHome', () => ({
  translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
  Matomo: { helper: { modalConfirm } },
  AjaxHelper: { post: () => Promise.resolve({}) },
  ContentBlock: defineComponent({ template: '<div><slot/></div>' }),
  ActivityIndicator: defineComponent({ template: '<div/>' }),
}));

// the interval field is replaced so the screen mounts without the whole form stack, but its
// inline help still renders, because that is where the purge link lives
vi.mock('CorePluginsAdmin', () => ({
  Field: defineComponent({
    name: 'FieldStub',
    template: '<div class="field"><slot name="inline-help"/></div>',
  }),
  PasswordConfirmation: defineComponent({
    name: 'PasswordConfirmationStub',
    props: {
      modelValue: { type: Boolean, default: false },
      requireDeleteConfirmation: { type: Boolean, default: false },
    },
    template: '<div><slot/></div>',
  }),
  SaveButton: defineComponent({ name: 'SaveButtonStub', template: '<button/>' }),
  Form: {},
}));

vi.mock('../ReportDeletionSettings/ReportDeletionSettings.store', () => ({
  default: {
    state: storeState,
    // the block is only on screen once one of the two deletion settings is enabled
    isEitherDeleteSectionEnabled: () => true,
    reloadDbStats: vi.fn(),
    savePurgeDataSettings: vi.fn(),
  },
}));

import ScheduleReportDeletion from './ScheduleReportDeletion.vue';

function mountSchedule() {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(ScheduleReportDeletion as any, {
    props: {
      isDataPurgeSettingsEnabled: true,
      deleteData: {
        config: { delete_logs_schedule_lowest_interval: 7 },
        lastRun: null,
        nextRunPretty: 'tomorrow',
      },
      scheduleDeletionOptions: {},
    },
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
        $sanitize: (html: string) => html,
      },
    },
  });
}

type Schedule = ReturnType<typeof mountSchedule>;

function openModals(wrapper: Schedule) {
  return wrapper.findAllComponents({ name: 'PasswordConfirmationStub' })
    .filter((modal) => modal.props('modelValue'));
}

describe('PrivacyManager/ScheduleReportDeletion', () => {
  beforeEach(() => {
    storeState.value.isModified = false;
    modalConfirm.mockClear();
  });

  // purging deletes there and then, rather than scheduling a deletion for later, so the
  // acknowledgement matters here more than it does on the settings above it
  it('asks for the deletion to be typed out before purging now', async () => {
    const wrapper = mountSchedule();

    await wrapper.find('#purgeDataNowLink').trigger('click');

    const [modal] = openModals(wrapper);
    expect(modal.props('requireDeleteConfirmation')).toBe(true);
  });

  it('asks for a password alone when saving the schedule', async () => {
    const wrapper = mountSchedule();

    await wrapper.findComponent({ name: 'SaveButtonStub' }).vm.$emit('confirm');

    const [modal] = openModals(wrapper);
    expect(modal.props('requireDeleteConfirmation')).toBe(false);
  });

  // an unsaved interval would not be the one the purge runs with, so it never gets that far
  it('asks for unsaved settings to be saved before purging', async () => {
    storeState.value.isModified = true;
    const wrapper = mountSchedule();

    await wrapper.find('#purgeDataNowLink').trigger('click');

    expect(modalConfirm).toHaveBeenCalledWith('#saveSettingsBeforePurge', expect.anything());
    expect(openModals(wrapper)).toHaveLength(0);
  });
});
