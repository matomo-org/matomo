/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  Matomo: { requiresPasswordConfirmation: true, postEvent: () => {} },
  AutoClearPassword: {},
  translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
  useExternalPluginComponent: vi.fn(() => null),
}));

// replaced so the modal can be mounted without pulling in the whole form stack
vi.mock('../Field/Field.vue', () => ({
  default: defineComponent({
    name: 'FieldStub',
    props: {
      name: { type: String, default: '' },
      id: { type: String, default: '' },
      modelValue: { type: String, default: '' },
      title: { type: String, default: '' },
      uiControlAttributes: { type: Object, default: () => ({}) },
    },
    template: '<div class="field" />',
  }),
}));

// the modal is opened and closed through Materialize's jQuery plugin, which the test
// environment does not load. Nothing here depends on what it does, only that it is callable.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
(window as any).$.fn.modal = function modal() { return this; };

import { Matomo, useExternalPluginComponent } from 'CoreHome';
import PasswordConfirmation from './PasswordConfirmation.vue';

function mountModal(props: Record<string, unknown> = {}, requiresPassword = true) {
  // read by a computed, so it has to be set before the component is created
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  (Matomo as any).requiresPasswordConfirmation = requiresPassword;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(PasswordConfirmation as any, {
    // the modal is never opened: showPasswordConfirmModal() drives Materialize, and everything
    // asserted here is state the closed component already holds
    props: { modelValue: false, ...props },
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
      },
    },
  });
}

function isConfirmDisabled(wrapper: ReturnType<typeof mountModal>) {
  return wrapper.find('.confirm-password-btn').attributes('disabled') === 'true';
}

// stands in for LoginSaml's re-authentication button, which replaces the password field for
// single sign-on users and confirms on their behalf with the token its tab produced
const altIdClicked = vi.fn();
const ALT_ID_TOKEN = 'reAuth_0123456789abcdef';
const AltIdStub = defineComponent({
  name: 'AltIdStub',
  emits: ['confirmed'],
  methods: {
    onClick() {
      altIdClicked();
      this.$emit('confirmed', ALT_ID_TOKEN);
    },
  },
  template: '<a href="" class="modal-action btn" @click="onClick"></a>',
});

// a component may render more than one root node, which used to leave $el a text node
const AltIdFragmentStub = defineComponent({
  ...AltIdStub,
  name: 'AltIdFragmentStub',
  template: '<span class="hint"></span><a href="" class="modal-action btn" @click="onClick"></a>',
});

async function mountModalWithAltId(props: Record<string, unknown> = {}, stub = AltIdStub) {
  (useExternalPluginComponent as ReturnType<typeof vi.fn>).mockReturnValue(stub);

  const wrapper = mountModal(props);
  // the component is normally announced while the modal opens, which these tests never do
  await wrapper.setData({
    altIdConfirmComponent: { plugin: 'LoginSaml', component: 'PasswordConfirmationReAuth' },
  });

  return wrapper;
}

function altIdWrapper(wrapper: ReturnType<typeof mountModal>) {
  return wrapper.find('.confirmPasswordModal__altIdConfirmation');
}

function altIdButton(wrapper: ReturnType<typeof mountModal>) {
  return altIdWrapper(wrapper).find('.btn');
}

describe('CorePluginsAdmin/PasswordConfirmation', () => {
  describe('without requireDeleteConfirmation', () => {
    it('does not render the typed confirmation at all', () => {
      expect(mountModal().find('.delete-confirmation-div').exists()).toBe(false);
    });

    it('keeps confirming gated on the password alone', async () => {
      const wrapper = mountModal();
      expect(isConfirmDisabled(wrapper)).toBe(true);

      await wrapper.setData({ passwordConfirmation: 'a password' });
      expect(isConfirmDisabled(wrapper)).toBe(false);
    });

    // Single sign-on users confirm through an injected identity component instead of the
    // password field, so an empty password is a valid confirmation for them. Gating onConfirm
    // on anything more than the typed word would lock those users out of every call site.
    it('still confirms with an empty password', () => {
      const wrapper = mountModal();

      wrapper.vm.onConfirm('');

      expect(wrapper.emitted('confirmed')).toEqual([['']]);
    });

    // enter means what pressing Confirm means, and Confirm is disabled without a password
    it('does not confirm on enter while the password is empty', () => {
      const wrapper = mountModal();

      wrapper.vm.onKeyPressConfirm({ keyCode: 13 });

      expect(wrapper.emitted('confirmed')).toBeUndefined();
    });

    it('confirms on enter once the password is given', async () => {
      const wrapper = mountModal();
      await wrapper.setData({ passwordConfirmation: 'a password' });

      wrapper.vm.onKeyPressConfirm({ keyCode: 13 });

      expect(wrapper.emitted('confirmed')).toEqual([['a password']]);
    });

    // the button is only styled as disabled, so activating it by keyboard still reaches the
    // handler and it has to refuse there too
    it('does not confirm when pressed while the password is empty', async () => {
      const wrapper = mountModal();

      await wrapper.find('.confirm-password-btn').trigger('click');

      expect(wrapper.emitted('confirmed')).toBeUndefined();
    });

    it('confirms when pressed once the password is given', async () => {
      const wrapper = mountModal();
      await wrapper.setData({ passwordConfirmation: 'a password' });

      await wrapper.find('.confirm-password-btn').trigger('click');

      expect(wrapper.emitted('confirmed')).toEqual([['a password']]);
    });
  });

  describe('with requireDeleteConfirmation', () => {
    it('renders the typed confirmation with an id derived from the password field', () => {
      const wrapper = mountModal({
        requireDeleteConfirmation: true,
        passwordFieldId: 'passwordCnil',
      });

      const field = wrapper.find('.delete-confirmation-div').findComponent({ name: 'FieldStub' });
      expect(field.props('id')).toBe('passwordCnilDeleteConfirmation');
      // the match is case sensitive, so a phone keyboard must not capitalise the empty field
      expect(field.props('uiControlAttributes')).toEqual({ autocapitalize: 'off' });
    });

    // the label is the only place the user is told what to type, so it has to name the very
    // string the match accepts - bolded rather than quoted, so the quotes are not mistaken for
    // part of the word
    it('bolds the word it accepts in the label', async () => {
      const wrapper = mountModal({ requireDeleteConfirmation: true });
      const field = wrapper.find('.delete-confirmation-div').findComponent({ name: 'FieldStub' });

      const [, placeholder] = field.props('title').split('|');
      expect(placeholder).toBe('<strong>delete</strong>');

      await wrapper.setData({
        passwordConfirmation: 'a password',
        deleteConfirmation: placeholder.replace(/<\/?strong>/g, ''),
      });
      expect(isConfirmDisabled(wrapper)).toBe(false);
    });

    it.each([
      ['nothing typed', ''],
      ['a capitalised word', 'Delete'],
      ['an upper case word', 'DELETE'],
      ['a padded word', ' delete '],
      ['something else entirely', 'remove'],
    ])('stays disabled with %s', async (_label, typed) => {
      const wrapper = mountModal({ requireDeleteConfirmation: true });

      await wrapper.setData({ passwordConfirmation: 'a password', deleteConfirmation: typed });

      expect(isConfirmDisabled(wrapper)).toBe(true);
    });

    it('stays disabled while the word is typed but the password is empty', async () => {
      const wrapper = mountModal({ requireDeleteConfirmation: true });

      await wrapper.setData({ deleteConfirmation: 'delete' });

      expect(isConfirmDisabled(wrapper)).toBe(true);
    });

    it('enables confirming once both are given', async () => {
      const wrapper = mountModal({ requireDeleteConfirmation: true });

      await wrapper.setData({ passwordConfirmation: 'a password', deleteConfirmation: 'delete' });

      expect(isConfirmDisabled(wrapper)).toBe(false);
    });

    it('needs only the word where re-authentication does not apply', async () => {
      const wrapper = mountModal({ requireDeleteConfirmation: true }, false);
      expect(isConfirmDisabled(wrapper)).toBe(true);

      await wrapper.setData({ deleteConfirmation: 'delete' });

      expect(isConfirmDisabled(wrapper)).toBe(false);
    });

    // the button is only styled as disabled, so it stays reachable by keyboard
    it('refuses to confirm while the word is missing', () => {
      const wrapper = mountModal({ requireDeleteConfirmation: true });

      wrapper.vm.onConfirm('a password');

      expect(wrapper.emitted('confirmed')).toBeUndefined();
    });

    it('confirms once the word is typed', async () => {
      const wrapper = mountModal({ requireDeleteConfirmation: true });
      await wrapper.setData({ deleteConfirmation: 'delete' });

      wrapper.vm.onConfirm('a password');

      expect(wrapper.emitted('confirmed')).toEqual([['a password']]);
    });

    it('clears both fields after confirming', async () => {
      const wrapper = mountModal({ requireDeleteConfirmation: true });
      await wrapper.setData({ passwordConfirmation: 'a password', deleteConfirmation: 'delete' });

      wrapper.vm.onConfirm('a password');

      expect(wrapper.vm.passwordConfirmation).toBe('');
      expect(wrapper.vm.deleteConfirmation).toBe('');
    });
  });

  describe('with an alternative identity confirmation component', () => {
    beforeEach(() => {
      altIdClicked.mockClear();
    });

    // hiding it outright left single sign-on users looking at nothing but Cancel, where
    // everyone else sees a Confirm button that is merely greyed out
    it('keeps the button on screen while the word is missing', async () => {
      const wrapper = await mountModalWithAltId({ requireDeleteConfirmation: true });

      expect(altIdButton(wrapper).isVisible()).toBe(true);
      expect(altIdWrapper(wrapper).classes())
        .toContain('confirmPasswordModal__altIdConfirmation--disabled');
    });

    it('lets the button through once the word is typed', async () => {
      const wrapper = await mountModalWithAltId({ requireDeleteConfirmation: true });

      await wrapper.setData({ deleteConfirmation: 'delete' });

      expect(altIdWrapper(wrapper).classes())
        .not.toContain('confirmPasswordModal__altIdConfirmation--disabled');
    });

    // pointer-events only stops the mouse. Without inert the greyed button is still one tab
    // away, and a sign-on round trip started from there would be discarded on the way back
    it('takes the greyed button out of the focus order while the word is missing', async () => {
      const wrapper = await mountModalWithAltId({ requireDeleteConfirmation: true });

      expect(altIdWrapper(wrapper).attributes('inert')).toBe('true');

      await wrapper.setData({ deleteConfirmation: 'delete' });

      expect(altIdWrapper(wrapper).attributes('inert')).toBeUndefined();
    });

    it('does nothing on enter while the word is missing', async () => {
      const wrapper = await mountModalWithAltId({ requireDeleteConfirmation: true });

      wrapper.vm.onKeyPressConfirm({ keyCode: 13 });

      expect(altIdClicked).not.toHaveBeenCalled();
      expect(wrapper.emitted('confirmed')).toBeUndefined();
    });

    // the component confirms for them, so enter has to start it rather than confirm with
    // the empty password sitting behind it
    it('presses the button on enter once the word is typed', async () => {
      const wrapper = await mountModalWithAltId({ requireDeleteConfirmation: true });
      await wrapper.setData({ deleteConfirmation: 'delete' });

      wrapper.vm.onKeyPressConfirm({ keyCode: 13 });

      expect(altIdClicked).toHaveBeenCalled();
      expect(wrapper.emitted('confirmed')).toEqual([[ALT_ID_TOKEN]]);
    });

    it('reaches the button of a component rendering more than one root node', async () => {
      const wrapper = await mountModalWithAltId(
        { requireDeleteConfirmation: true },
        AltIdFragmentStub,
      );
      expect(altIdWrapper(wrapper).classes())
        .toContain('confirmPasswordModal__altIdConfirmation--disabled');

      await wrapper.setData({ deleteConfirmation: 'delete' });
      wrapper.vm.onKeyPressConfirm({ keyCode: 13 });

      expect(altIdClicked).toHaveBeenCalled();
      expect(wrapper.emitted('confirmed')).toEqual([[ALT_ID_TOKEN]]);
    });

    it('leaves the button alone while it reports itself disabled', async () => {
      const wrapper = await mountModalWithAltId({ requireDeleteConfirmation: true });
      await wrapper.setData({ deleteConfirmation: 'delete' });
      altIdButton(wrapper).element.setAttribute('disabled', 'true');

      wrapper.vm.onKeyPressConfirm({ keyCode: 13 });

      expect(altIdClicked).not.toHaveBeenCalled();
    });

    // by far the most common case: every confirmation that schedules no deletion at all
    it('is left untouched without requireDeleteConfirmation', async () => {
      const wrapper = await mountModalWithAltId();

      expect(altIdWrapper(wrapper).classes())
        .not.toContain('confirmPasswordModal__altIdConfirmation--disabled');

      wrapper.vm.onKeyPressConfirm({ keyCode: 13 });

      expect(altIdClicked).toHaveBeenCalled();
    });
  });
});
