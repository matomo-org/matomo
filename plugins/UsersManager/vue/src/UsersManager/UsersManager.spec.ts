/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import UsersManager from './UsersManager.vue';

const { postEvent, urlParsed } = vi.hoisted(() => ({
  postEvent: vi.fn(),
  urlParsed: { value: {} as Record<string, unknown> },
}));

// 'CoreHome' and 'CorePluginsAdmin' are build externals rather than real modules
vi.mock('CoreHome', () => ({
  ContentIntro: {},
  Tooltips: {},
  EnrichedHeadline: { template: '<div><slot /></div>' },
  Matomo: {
    postEvent: (...args: unknown[]) => postEvent(...args),
    helper: { lazyScrollToContent: () => undefined },
  },
  MatomoUrl: { urlParsed },
  AjaxHelper: { fetch: () => Promise.reject(new Error('not exercised here')) },
  translate: (key: string) => key,
  NotificationsStore: { show: () => undefined, remove: () => undefined },
  useExternalPluginComponent: () => ({ template: '<div class="externalComponent" />' }),
}));

vi.mock('CorePluginsAdmin', () => ({
  Field: { template: '<div />' },
}));

// stubbed at the module level, not just at render, so their own imports are never pulled in
vi.mock('../PagedUsersList/PagedUsersList.vue', () => ({ default: { template: '<div />' } }));
vi.mock('../UserEditForm/UserEditForm.vue', () => ({ default: { template: '<div />' } }));

interface UsersManagerVm {
  isInviting: boolean;
  isEditing: boolean;
}

function mountManager(urlParams: Record<string, unknown> = {}) {
  urlParsed.value = urlParams;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(UsersManager as any, {
    shallow: true,
    global: {
      mocks: {
        translate: (key: string) => key,
        externalRawLink: (url: string) => url,
        externalLink: (url: string) => url,
      },
    },
    props: {
      currentUserRole: 'superuser',
      initialSiteName: 'Site 1',
      initialSiteId: '1',
      accessLevels: [],
      filterAccessLevels: [],
      filterStatusLevels: [],
      activatedPlugins: [],
      inviteTokenExpiryDays: '7',
      passwordStrengthValidationRules: [],
    },
  });

  return wrapper.vm as unknown as UsersManagerVm;
}

describe('UsersManager/UsersManager', () => {
  beforeEach(() => {
    postEvent.mockReset();
  });

  it('shows the user list when no screen is requested', () => {
    const vm = mountManager();

    expect(vm.isInviting).toBe(false);
    expect(vm.isEditing).toBe(false);
  });

  // showadduser is linked from the no-data screen and the quick links widget, both labelled
  // "Invite New User"; it used to open the edit form for a null user instead.
  it('opens the invite screen when showadduser is requested', () => {
    const vm = mountManager({ showadduser: '1' });

    expect(vm.isInviting).toBe(true);
    expect(vm.isEditing).toBe(false);
  });

  it('lets a plugin veto the invite screen through UsersManager.initAddUser', () => {
    postEvent.mockImplementation((name: string, params: { isAllowed: boolean }) => {
      if (name === 'UsersManager.initAddUser') {
        params.isAllowed = false; // eslint-disable-line no-param-reassign
      }
    });

    const vm = mountManager({ showadduser: '1' });

    expect(postEvent).toHaveBeenCalledWith('UsersManager.initAddUser', { isAllowed: false });
    expect(vm.isInviting).toBe(false);
  });
});
