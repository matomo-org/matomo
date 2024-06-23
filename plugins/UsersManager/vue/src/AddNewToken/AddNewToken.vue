<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="translate('UsersManager_AuthTokens')">
    <p>
      {{ translate('UsersManager_TokenAuthIntro') }}
    </p>

    <br v-if="noDescription"/>
    <div class="alert alert-danger" v-if="noDescription">
      {{ translate('General_Description') }}: {{ translate('General_ValidatorErrorEmptyValue') }}
    </div>

    <form
      :action="addNewTokenFormUrl"
      method="post"
      class="addTokenForm"
    >
      <Field
        uicontrol="text"
        name="description"
        :title="translate('General_Description')"
        :maxlength="100"
        :required="true"
        :inline-help="translate('UsersManager_AuthTokenPurpose')"
        v-model="tokenDescription"
      />

      <Field
        uicontrol="checkbox"
        name="secure_only"
        :title="translate('UsersManager_OnlyAllowSecureRequests')"
        :required="false"
        :inline-help=secureOnlyHelp
        v-model="tokenSecureOnly"
        :disabled=forceSecureOnlyCalc
      />

      <input type="hidden" :value="formNonce" name="nonce">

      <input
        type="submit"
        :value="translate('UsersManager_CreateNewToken')"
        class="btn"
        style="margin-right:3.5px"
      />

      <span v-html="$sanitize(cancelLink)"></span>
    </form>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, ContentBlock, MatomoUrl } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface AddNewTokenState {
  tokenDescription: string;
  tokenSecureOnly: boolean;
}

export default defineComponent({
  props: {
    formNonce: String,
    noDescription: Boolean,
    forceSecureOnly: Boolean,
  },
  components: {
    ContentBlock,
    Field,
  },
  data(): AddNewTokenState {
    return {
      tokenDescription: '',
      tokenSecureOnly: true,
    };
  },
  computed: {
    addNewTokenFormUrl() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'addNewToken',
      })}`;
    },
    cancelLink() {
      const backlink = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'userSecurity',
      })}`;

      return translate(
        'General_OrCancel',
        `<a class='entityCancelLink' href='${backlink}'>`,
        '</a>',
      );
    },
    forceSecureOnlyCalc() {
      return this.forceSecureOnly;
    },
    secureOnlyHelp() {
      return (this.forceSecureOnly ? translate('UsersManager_AuthTokenSecureOnlyHelpForced')
        : translate('UsersManager_AuthTokenSecureOnlyHelp'));
    },
  },
});
</script>
