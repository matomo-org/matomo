/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  AjaxHelper: { post: vi.fn(() => Promise.resolve({})) },
  NotificationsStore: { show: vi.fn(() => 'resendInvite'), scrollToNotification: vi.fn() },
  translate: (key: string) => key,
}));

vi.mock('CorePluginsAdmin', () => ({
  PasswordConfirmation: defineComponent({
    name: 'PasswordConfirmationStub',
    props: { modelValue: Boolean },
    emits: ['confirmed', 'update:modelValue'],
    template: '<div class="password-confirmation-stub" />',
  }),
}));

// the modal is opened and closed through Materialize's jQuery plugin, which the test
// environment does not load
// eslint-disable-next-line @typescript-eslint/no-explicit-any
(window as any).$.fn.modal = function modal() { return this; };

import { AjaxHelper } from 'CoreHome';
import ResendInviteModal from './ResendInviteModal.vue';

function mountModal() {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(ResendInviteModal as any, {
    props: { user: { login: 'invitee', email: 'invitee@example.com' }, inviteTokenExpiryDays: '7' },
    global: {
      mocks: {
        translate: (key: string) => key,
        $sanitize: (value: string) => value,
      },
    },
  });
}

async function confirmResend(password: string) {
  const wrapper = mountModal();
  await wrapper.find('.btn-resend').trigger('click');
  wrapper.findComponent({ name: 'PasswordConfirmationStub' }).vm.$emit('confirmed', password);
  await wrapper.vm.$nextTick();
  return wrapper;
}

describe('UsersManager/ResendInviteModal', () => {
  beforeEach(() => {
    (AjaxHelper.post as ReturnType<typeof vi.fn>).mockClear();
  });

  it('resends the invite with the confirmed password', async () => {
    await confirmResend('a password');

    expect(AjaxHelper.post).toHaveBeenCalledTimes(1);
    expect(AjaxHelper.post).toHaveBeenCalledWith(
      { method: 'UsersManager.resendInvite', userLogin: 'invitee' },
      { passwordConfirmation: 'a password' },
    );
  });

  // users who do not need to confirm with a password (e.g. LDAP or single sign-on users)
  // confirm with an empty password, which the API accepts for them
  it('still resends the invite when confirmed with an empty password', async () => {
    await confirmResend('');

    expect(AjaxHelper.post).toHaveBeenCalledTimes(1);
    expect(AjaxHelper.post).toHaveBeenCalledWith(
      { method: 'UsersManager.resendInvite', userLogin: 'invitee' },
      { passwordConfirmation: '' },
    );
  });
});
