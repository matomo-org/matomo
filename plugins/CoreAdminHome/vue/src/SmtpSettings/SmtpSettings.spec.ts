/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const { post } = vi.hoisted(() => ({ post: vi.fn(() => Promise.resolve()) }));

vi.mock('CoreHome', () => ({
  AjaxHelper: { post },
  AutoClearPassword: {},
  ContentBlock: { template: '<div><slot></slot></div>' },
  NotificationsStore: { show: vi.fn(), scrollToNotification: vi.fn() },
  translate: (key: string) => key,
}));

vi.mock('CorePluginsAdmin', () => ({
  Field: {
    props: ['name', 'modelValue', 'inlineHelp'],
    emits: ['update:modelValue'],
    template: '<input :name="name" :value="modelValue" :data-help="inlineHelp"'
      + ' @input="$emit(\'update:modelValue\', $event.target.value)" />',
  },
  Form: {},
  PasswordConfirmation: {
    emits: ['confirmed'],
    template: '<button class="confirm" @click="$emit(\'confirmed\', \'my-password\')"></button>',
  },
  SaveButton: { template: '<button></button>' },
}));

import SmtpSettings from './SmtpSettings.vue';

function mountWithStoredOAuthSettings(type = 'Xoauth2', storedMail: Record<string, string> = {}) {
  return mount(SmtpSettings, {
    props: {
      mail: {
        transport: 'smtp',
        host: 'smtp.example.test',
        port: 587,
        type,
        username: 'sender@example.test',
        password: '',
        oauth_token_url: 'https://login.example.test/token',
        oauth_client_id: 'the-client-id',
        oauth_client_secret: '*****',
        oauth_scope: 'https://mail.example.test/.default',
        noreply_email_address: 'noreply@example.test',
        noreply_email_name: 'Matomo',
        encryption: 'tls',
        ...storedMail,
      },
      mailTypes: {},
      mailEncryptions: {},
    },
    global: { config: { globalProperties: { translate: (key: string) => key } as never } },
  });
}

async function save(wrapper: ReturnType<typeof mountWithStoredOAuthSettings>) {
  await wrapper.find('.confirm').trigger('click');

  return post.mock.calls[post.mock.calls.length - 1][1] as Record<string, unknown>;
}

describe('CoreAdminHome/SmtpSettings.vue', () => {
  beforeEach(() => {
    post.mockClear();
  });

  it('does not send the stored client secret back when it was not changed', async () => {
    const posted = await save(mountWithStoredOAuthSettings());

    expect(posted.mailOAuthTokenUrl).toBe('https://login.example.test/token');
    expect(posted).not.toHaveProperty('mailOAuthClientSecret');
  });

  it('sends the client secret once it was entered', async () => {
    const wrapper = mountWithStoredOAuthSettings();

    await wrapper.find('[name="mailOAuthClientSecret"]').setValue('a-new-secret');

    expect((await save(wrapper)).mailOAuthClientSecret).toBe('a-new-secret');
  });

  it('does not send a client secret entered before switching to the certificate', async () => {
    const wrapper = mountWithStoredOAuthSettings();

    await wrapper.find('[name="mailOAuthClientSecret"]').setValue('a-new-secret');
    await wrapper.find('[name="mailOAuthCredentialType"]').setValue('certificate');
    const posted = await save(wrapper);

    expect(posted.mailOAuthCredentialType).toBe('certificate');
    expect(posted).not.toHaveProperty('mailOAuthClientSecret');
  });

  it('drops the stored client secret when the SMTP server changes', async () => {
    const wrapper = mountWithStoredOAuthSettings();

    await wrapper.find('[name="mailHost"]').setValue('another.example.test');

    expect((await save(wrapper)).mailOAuthClientSecret).toBe('');
  });

  it.each([
    ['secret', {}],
    ['secret', { oauth_credential_type: 'secret' }],
    ['certificate', { oauth_credential_type: ' Certificate ' }],
  ])('posts the credential type %s stored as %j', async (expected, storedMail) => {
    const posted = await save(mountWithStoredOAuthSettings('Xoauth2', storedMail));

    expect(posted.mailOAuthCredentialType).toBe(expected);
  });

  it('only shows what applies to the selected authentication method', () => {
    const oauthFields = [
      'mailOAuthTokenUrl',
      'mailOAuthClientId',
      'mailOAuthCredentialType',
      'mailOAuthClientSecret',
      'mailOAuthScope',
    ];
    const password = mountWithStoredOAuthSettings('Login');
    const oauth = mountWithStoredOAuthSettings('Xoauth2');
    const fields = (wrapper: typeof oauth) => wrapper
      .findAll('[name="mailPassword"], [name^="mailOAuth"]').map((field) => field.attributes('name'));

    expect(fields(password)).toEqual(['mailPassword']);
    expect(fields(oauth)).toEqual(oauthFields);
    // FormField does not restore an inline help that was removed, so every method needs a help text
    expect(password.find('[name="mailType"]').attributes('data-help')).toBe('General_OnlyUsedIfUserPwdIsSet');
    expect(oauth.find('[name="mailType"]').attributes('data-help')).toBe('CoreAdminHome_SmtpOAuthAuthenticationHelp');
  });
});
