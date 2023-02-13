<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        name="post_only"
        :title="translate('UsersManager_OnlyAllowPostRequests')"
        :maxlength="100"
        :required="false"
        :inline-help="translate('UsersManager_AuthTokenPostOnlyHelp')"
        v-model="tokenPostOnly"
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
  tokenPostOnly: boolean;
}

export default defineComponent({
  props: {
    noDescription: Boolean,
    formNonce: String,
  },
  components: {
    ContentBlock,
    Field,
  },
  data(): AddNewTokenState {
    return {
      tokenDescription: '',
      tokenPostOnly: true,
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
  },
});
</script>
