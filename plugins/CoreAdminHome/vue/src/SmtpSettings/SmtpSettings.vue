<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
          name="mailType"
          v-model="mailType"
          :title="translate('General_AuthenticationMethodSmtp')"
          :options="mailTypes"
          :inline-help="translate('General_OnlyUsedIfUserPwdIsSet')"
        />

        <Field
          uicontrol="text"
          name="mailUsername"
          v-model="mailUsername"
          :title="translate('General_SmtpUsername')"
          :inline-help="translate('General_OnlyEnterIfRequired')"
          :autocomplete="'off'"
        />

        <Field
          uicontrol="password"
          name="mailPassword"
          :model-value="mailPassword"
          @update:model-value="onMailPasswordChange($event)"
          @click="!passwordChanged && $event.target.select();"
          :title="translate('General_SmtpPassword')"
          :inline-help="passwordHelp"
          :autocomplete="'off'"
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
          name="mailEncryption"
          v-model="mailEncryption"
          :title="translate('General_SmtpEncryption')"
          :options="mailEncryptions"
          :inline-help="translate('General_EncryptedSmtpTransport')"
        />
      </div>

      <SaveButton
        @confirm="save()"
        :saving="isLoading"
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
} from 'CoreHome';
import {
  SaveButton,
  Form,
  Field,
} from 'CorePluginsAdmin';

interface SmtpSettingsState {
  isLoading: boolean;
  enabled: boolean;
  mailHost: string;
  passwordChanged: boolean;
  mailPort: number;
  mailType: string;
  mailUsername: string;
  mailPassword: string;
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
      enabled: mail.transport === 'smtp',
      mailHost: mail.host,
      passwordChanged: false,
      mailPort: mail.port,
      mailType: mail.type,
      mailUsername: mail.username,
      mailPassword: mail.password ? '******' : '',
      mailFromAddress: mail.noreply_email_address,
      mailFromName: mail.noreply_email_name,
      mailEncryption: mail.encryption,
    };
  },
  components: {
    ContentBlock,
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  computed: {
    passwordHelp() {
      const part1 = `${translate('General_OnlyEnterIfRequiredPassword')}<br/>`;
      const part2 = `${translate('General_WarningPasswordStored', '<strong>', '</strong>')}<br/>`;
      return `${part1}\n${part2}`;
    },
  },
  methods: {
    onUpdateMailHost(newValue: string) {
      this.mailHost = newValue;

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
    save() {
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
      };

      if (this.passwordChanged) {
        mailSettings.mailPassword = this.mailPassword;
      }

      AjaxHelper.post(
        { module: 'CoreAdminHome', action: 'setMailSettings' },
        mailSettings,
        { withTokenInUrl: true },
      ).then(() => {
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
