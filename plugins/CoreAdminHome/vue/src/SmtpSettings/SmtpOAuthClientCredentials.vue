<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <Field
      uicontrol="text"
      name="mailOAuthTokenUrl"
      :model-value="modelValue.tokenUrl"
      @update:model-value="updateSettingTiedToSecret({ tokenUrl: $event })"
      :title="translate('CoreAdminHome_SmtpOAuthTokenUrl')"
      :inline-help="tokenUrlHelp"
      :autocomplete="'off'"
    />

    <Field
      uicontrol="text"
      name="mailOAuthClientId"
      :model-value="modelValue.clientId"
      @update:model-value="updateSettingTiedToSecret({ clientId: $event })"
      :title="translate('CoreAdminHome_SmtpOAuthClientId')"
      :inline-help="translate('CoreAdminHome_SmtpOAuthClientIdHelp')"
      :autocomplete="'off'"
    />

    <Field
      uicontrol="password"
      name="mailOAuthClientSecret"
      :model-value="clientSecret"
      @update:model-value="onClientSecretChange($event)"
      @click="!clientSecretChanged && $event.target.select();"
      :title="translate('CoreAdminHome_SmtpOAuthClientSecret')"
      :inline-help="clientSecretHelp"
      :autocomplete="'off'"
      v-auto-clear-password
    />

    <Field
      uicontrol="text"
      name="mailOAuthScope"
      :model-value="modelValue.scope"
      @update:model-value="updateSettingTiedToSecret({ scope: $event })"
      :title="translate('CoreAdminHome_SmtpOAuthScope')"
      :inline-help="scopeHelp"
      :autocomplete="'off'"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate, AutoClearPassword } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

/**
 * The settings of the OAuth 2.0 client credentials flow. `clientSecret` is only set once the
 * user changed it, so an unchanged secret is never sent back to the server.
 */
export interface SmtpOAuthClientCredentialsSettings {
  tokenUrl: string;
  clientId: string;
  scope: string;
  clientSecret?: string;
}

export default defineComponent({
  props: {
    modelValue: {
      type: Object as PropType<SmtpOAuthClientCredentialsSettings>,
      required: true,
    },
    hasStoredClientSecret: Boolean,
  },
  emits: ['update:modelValue'],
  components: {
    Field,
  },
  directives: {
    AutoClearPassword,
  },
  computed: {
    clientSecretChanged() {
      return this.modelValue.clientSecret !== undefined;
    },
    clientSecret() {
      if (this.modelValue.clientSecret !== undefined) {
        return this.modelValue.clientSecret;
      }

      return this.hasStoredClientSecret ? '******' : '';
    },
    tokenUrlHelp() {
      return translate(
        'CoreAdminHome_SmtpOAuthTokenUrlHelp',
        'https://login.microsoftonline.com/&lt;tenant-id&gt;/oauth2/v2.0/token',
      );
    },
    clientSecretHelp() {
      return translate('CoreAdminHome_SmtpOAuthClientSecretHelp', '<strong>', '</strong>');
    },
    scopeHelp() {
      return translate('CoreAdminHome_SmtpOAuthScopeHelp', 'https://outlook.office365.com/.default');
    },
  },
  methods: {
    update(changes: Partial<SmtpOAuthClientCredentialsSettings>) {
      this.$emit('update:modelValue', { ...this.modelValue, ...changes });
    },
    updateSettingTiedToSecret(changes: Partial<SmtpOAuthClientCredentialsSettings>) {
      if (this.clientSecretChanged) {
        this.update(changes);
        return;
      }

      // the stored client secret must not be sent to another token URL,
      // or be used with another client ID or scope
      this.update({ ...changes, clientSecret: '' });
    },
    onClientSecretChange(clientSecret: string) {
      this.update({ clientSecret });
    },
  },
});
</script>
