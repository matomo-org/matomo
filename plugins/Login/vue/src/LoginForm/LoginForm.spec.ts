/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import LoginForm from './LoginForm.vue';

describe('LoginForm.vue', () => {
  let wrapper: ReturnType<typeof mount>;

  beforeEach(() => {
    wrapper = mount(LoginForm, {
      props: {
        rememberMe: true,
        nonceToken: 'test-nonce',
      },
    });
  });

  it('renders the form with expected fields', () => {
    expect(wrapper.find('#login_form').exists()).toBe(true);
    expect(wrapper.find('#login_form_login').exists()).toBe(true);
    expect(wrapper.find('#login_form_password').exists()).toBe(true);
    expect(wrapper.find('#login_form_nonce').element.getAttribute('value')).toBe('test-nonce');
  });

  it('initially disables submit button when form is incomplete', () => {
    const submitBtn = wrapper.find('#login_form_submit');
    expect(submitBtn.classes()).toContain('disabled');
  });

  it('enables submit button when form is complete', async () => {
    await wrapper.find('#login_form_login').setValue('testuser');
    await wrapper.find('#login_form_password').setValue('secret');

    const submitBtn = wrapper.find('#login_form_submit');
    expect(submitBtn.classes()).not.toContain('disabled');
  });

  it('checks that rememberMe checkbox is checked based on prop', () => {
    const checkbox = wrapper.find('#login_form_rememberme');
    expect((checkbox.element as HTMLInputElement).checked).toBe(true);
  });
});
