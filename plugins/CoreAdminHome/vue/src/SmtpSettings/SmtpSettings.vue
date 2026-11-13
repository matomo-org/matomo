<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('CoreAdminHome_EmailServerSettings')"
    anchor="mailSettings"
  >
    <div v-form>
      <Field
        uicontrol="checkbox"
        name="mailUseSmtp"
        v-model="enabled"
        :title="translate('General_UseSMTPServerForEmail')"
        :inline-help="translate('General_SelectYesIfYouWantToSendEmailsViaServer')"
      />

      <div id="smtpSettings" v-show="enabled">

        <Field
          uicontrol="text"
          name="mailHost"
          :model-value="mailHost"
          @update:model-value="onUpdateMailHost($event)"
          :title="translate('General_SmtpServerAddress')"
        />

        <Field
          uicontrol="text"
          name="mailPort"
          v-model="mailPort"
          :title="translate('General_SmtpPort')"
          :inline-help="translate('General_OptionalSmtpPort')"
        />

        <Field
          uicontrol="select"
          name="mailEncryption"
          v-model="mailEncryption"
          :title="translate('General_SmtpEncryption')"
          :options="mailEncryptions"
          :inline-help="translate('General_EncryptedSmtpTransport')"
        />

        <Field
          uicontrol="text"
          name="mailFromAddress"
          v-model="mailFromAddress"
          :title="translate('General_SmtpFromAddress')"
          :inline-help="translate('General_SmtpFromEmailHelp', mailHost)"
          :autocomplete="'off'"
        />

        <Field
          uicontrol="text"
          name="mailFromName"
          v-model="mailFromName"
          :title="translate('General_SmtpFromName')"
          :inline-help="translate('General_NameShownInTheSenderColumn')"
          :autocomplete="'off'"
        />

        <Field
          uicontrol="select"
          name="mailType"
          v-model="mailType"
          :title="translate('General_AuthenticationMethodSmtp')"
          :options="mailTypes"
          :inline-help="mailTypeHelp"
        />

        <Field
          uicontrol="text"
          name="mailUsername"
          v-model="mailUsername"
          :title="translate('General_SmtpUsername')"
          :inline-help="usernameHelp"
          :autocomplete="'off'"
        />

        <Field
          v-if="!isOAuth"
          uicontrol="password"
          name="mailPassword"
          :model-value="mailPassword"
          @update:model-value="onMailPasswordChange($event)"
          @click="!passwordChanged && $event.target.select();"
          :title="translate('General_SmtpPassword')"
          :inline-help="passwordHelp"
          :autocomplete="'off'"
          v-auto-clear-password
        />

        <SmtpOAuthClientCredentials
          v-else
          id="smtpOAuthSettings"
          v-model="oauthSettings"
          :has-stored-client-secret="savedOAuthSettings.hasClientSecret"
          :certificate-path="savedOAuthSettings.certificatePath"
        />
      </div>

      <SaveButton
        @confirm="showPasswordConfirmation = true"
        :saving="isLoading"
      />

      <PasswordConfirmation
        v-model="showPasswordConfirmation"
        @confirmed="save($event)"
      />
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  translate,
  NotificationsStore,
  AjaxHelper,
  AutoClearPassword,
} from 'CoreHome';
import {
  SaveButton,
  Form,
  Field,
  PasswordConfirmation,
} from 'CorePluginsAdmin';
import SmtpOAuthClientCredentials, {
  SmtpOAuthClientCredentialsSettings,
} from './SmtpOAuthClientCredentials.vue';

// what the config file holds since the page was loaded or last saved
interface SavedOAuthSettings {
  host: string;
  tokenUrl: string;
  clientId: string;
  scope: string;
  certificatePath: string;
  hasClientSecret: boolean;
}

interface SmtpSettingsState {
  isLoading: boolean;
  showPasswordConfirmation: boolean;
  enabled: boolean;
  mailHost: string;
  passwordChanged: boolean;
  mailPort: number;
  mailType: string;
  mailUsername: string;
  mailPassword: string;
  oauthSettings: SmtpOAuthClientCredentialsSettings;
  savedOAuthSettings: SavedOAuthSettings;
  mailFromAddress: string;
  mailFromName: string;
  mailEncryption: string;
}

interface MailProperty {
  transport: string;
  host: string;
  port: number;
  type: string;
  username: string;
  password: string;
  oauth_token_url: string;
  oauth_client_id: string;
  oauth_client_secret: string;
  oauth_scope: string;
  oauth_credential_type?: string;
  oauth_certificate_path?: string;
  noreply_email_address: string;
  noreply_email_name: string;
  encryption: string;
}

export default defineComponent({
  props: {
    mail: {
      type: Object,
      required: true,
    },
    mailTypes: {
      type: Object,
      required: true,
    },
    mailEncryptions: {
      type: Object,
      required: true,
    },
  },
  data(): SmtpSettingsState {
    const mail = this.mail as MailProperty;
    return {
      isLoading: false,
      showPasswordConfirmation: false,
      enabled: mail.transport === 'smtp',
      mailHost: mail.host,
      passwordChanged: false,
      mailPort: mail.port,
      mailType: mail.type,
      mailUsername: mail.username,
      mailPassword: mail.password ? '******' : '',
      oauthSettings: {
        tokenUrl: mail.oauth_token_url,
        clientId: mail.oauth_client_id,
        scope: mail.oauth_scope,
        credentialType: (mail.oauth_credential_type || '').trim().toLowerCase() === 'certificate'
          ? 'certificate'
          : 'secret',
      },
      savedOAuthSettings: {
        host: mail.host,
        tokenUrl: mail.oauth_token_url,
        clientId: mail.oauth_client_id,
        scope: mail.oauth_scope,
        certificatePath: mail.oauth_certificate_path || '',
        hasClientSecret: !!mail.oauth_client_secret,
      },
      mailFromAddress: mail.noreply_email_address,
      mailFromName: mail.noreply_email_name,
      mailEncryption: mail.encryption,
    };
  },
  components: {
    ContentBlock,
    Field,
    SaveButton,
    PasswordConfirmation,
    SmtpOAuthClientCredentials,
  },
  directives: {
    Form,
    AutoClearPassword,
  },
  computed: {
    isOAuth() {
      return this.mailType === 'Xoauth2';
    },
    mailTypeHelp() {
      return translate(
        this.isOAuth ? 'CoreAdminHome_SmtpOAuthAuthenticationHelp' : 'General_OnlyUsedIfUserPwdIsSet',
      );
    },
    usernameHelp() {
      return translate(
        this.isOAuth ? 'CoreAdminHome_SmtpOAuthUsernameHelp' : 'General_OnlyEnterIfRequired',
      );
    },
    passwordHelp() {
      const part1 = `${translate('General_OnlyEnterIfRequiredPassword')}<br/>`;
      const part2 = `${translate('General_WarningPasswordStored', '<strong>', '</strong>')}<br/>`;
      return `${part1}\n${part2}`;
    },
  },
  methods: {
    onUpdateMailHost(newValue: string) {
      this.mailHost = newValue;

      // neither the stored client secret nor the stored password may be used with another server
      if (this.oauthSettings.clientSecret === undefined) {
        this.oauthSettings = { ...this.oauthSettings, clientSecret: '' };
      }

      if (this.passwordChanged) {
        return;
      }

      this.mailPassword = '';
      this.passwordChanged = true;
    },
    onMailPasswordChange(newValue: string) {
      this.mailPassword = newValue;
      this.passwordChanged = true;
    },
    updateSavedOAuthSettings(postedClientSecret?: string) {
      const saved = this.savedOAuthSettings;
      const current = {
        host: this.mailHost,
        tokenUrl: this.oauthSettings.tokenUrl,
        clientId: this.oauthSettings.clientId,
        scope: this.oauthSettings.scope,
      };
      const changed = (Object.keys(current) as (keyof typeof current)[])
        .some((key) => `${saved[key] || ''}`.trim() !== `${current[key] || ''}`.trim());

      // like the server, which drops the certificate settings and the client secret when one of
      // these changed, unless a client secret was sent
      this.savedOAuthSettings = {
        ...current,
        certificatePath: changed ? '' : saved.certificatePath,
        hasClientSecret: postedClientSecret !== undefined
          ? postedClientSecret !== ''
          : !changed && saved.hasClientSecret,
      };

      // the saved client secret must be entered again before it is sent to another token URL
      this.oauthSettings = { ...this.oauthSettings, clientSecret: undefined };
    },
    save(password: string) {
      this.isLoading = true;

      const mailSettings: Record<string, unknown> = {
        mailUseSmtp: this.enabled ? '1' : '0',
        mailPort: this.mailPort,
        mailHost: this.mailHost,
        mailType: this.mailType,
        mailUsername: this.mailUsername,
        mailFromAddress: this.mailFromAddress,
        mailFromName: this.mailFromName,
        mailEncryption: this.mailEncryption,
        mailOAuthTokenUrl: this.oauthSettings.tokenUrl,
        mailOAuthClientId: this.oauthSettings.clientId,
        mailOAuthScope: this.oauthSettings.scope,
        mailOAuthCredentialType: this.oauthSettings.credentialType,
        passwordConfirmation: password,
      };

      if (this.passwordChanged) {
        mailSettings.mailPassword = this.mailPassword;
      }

      // a secret entered before switching to the certificate is not used, so it isn't stored either
      if (this.oauthSettings.clientSecret !== undefined && this.oauthSettings.credentialType === 'secret') {
        mailSettings.mailOAuthClientSecret = this.oauthSettings.clientSecret;
      }

      AjaxHelper.post(
        { module: 'CoreAdminHome', action: 'setMailSettings' },
        mailSettings,
        { withTokenInUrl: true },
      ).then(() => {
        this.updateSavedOAuthSettings(mailSettings.mailOAuthClientSecret as string | undefined);

        const notificationInstanceId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    },
  },
});
</script>
