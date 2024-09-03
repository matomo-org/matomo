<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="ui-confirm" id="installPluginByUpload" v-plugin-upload>
    <h2>{{ translate('Marketplace_TeaserExtendPiwikByUpload') }}</h2>

    <div v-if="isPluginUploadEnabled">
      <p class="description"> {{ translate('Marketplace_AllowedUploadFormats') }} </p>

      <form
        enctype="multipart/form-data"
        method="post"
        id="uploadPluginForm"
        :action="uploadPluginAction"
      >
        <input type="file" name="pluginZip" :data-max-size="uploadLimit">
        <br />
        <Field
          uicontrol="password"
          name="confirmPassword"
          autocomplete="off"
          :title="translate('Login_ConfirmPasswordToContinue')"
          v-model="confirmPassword"
        />

        <input
          class="startUpload btn"
          type="submit"
          :value="translate('Marketplace_UploadZipFile')"
        />
      </form>
    </div>
    <div v-else>
      <p
        class="description"
        v-html="$sanitize(translate('Marketplace_PluginUploadDisabled'))"
      ></p>
      <pre>[General]
  enable_plugin_upload = 1</pre>
      <input role="yes" type="button" :value="translate('General_Ok')"/>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl } from 'CoreHome';
import Field from '../Field/Field.vue';
import PluginUpload from '../Plugins/PluginUpload';

interface UploadPluginDialogState {
  confirmPassword: string;
}

export default defineComponent({
  props: {
    isPluginUploadEnabled: Boolean,
    uploadLimit: [String, Number],
    installNonce: String,
  },
  components: {
    Field,
  },
  directives: {
    PluginUpload,
  },
  data(): UploadPluginDialogState {
    return {
      confirmPassword: '',
    };
  },
  computed: {
    uploadPluginAction() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'CorePluginsAdmin',
        action: 'uploadPlugin',
        nonce: this.installNonce,
      })}`;
    },
  },
});
</script>
