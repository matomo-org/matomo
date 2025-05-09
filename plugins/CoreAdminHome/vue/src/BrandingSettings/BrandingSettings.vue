<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('CoreAdminHome_BrandingSettings')"
    anchor="brandingSettings"
  >
    <div v-form>
      <p>{{ translate('CoreAdminHome_CustomLogoHelpText') }}</p>

      <Field
        name="useCustomLogo"
        uicontrol="checkbox"
        :model-value="enabled"
        @update:model-value="onUseCustomLogoChange($event)"
        :title="translate('CoreAdminHome_UseCustomLogo')"
        :inline-help="help"
      />

      <div id="logoSettings" v-show="enabled">
        <form
          id="logoUploadForm"
          ref="logoUploadForm"
          method="post"
          enctype="multipart/form-data"
          action="index.php?module=CoreAdminHome&format=json&action=uploadCustomLogo"
        >
          <div v-if="fileUploadEnabled">
            <input type="hidden" name="token_auth" :value="tokenAuth"/>
            <input type="hidden" name="force_api_session" value="1"/>

            <div v-if="logosWriteable">
              <transition name="fade-out">
                <div class="alert alert-warning uploaderror" v-if="showUploadError">
                  {{ translate('CoreAdminHome_LogoUploadFailed') }}
                </div>
              </transition>

              <Field
                uicontrol="file"
                name="customLogo"
                :model-value="customLogo"
                @update:model-value="onCustomLogoChange($event)"
                :title="translate('CoreAdminHome_LogoUpload')"
                :inline-help="translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '110')"
              />

              <div class="row">
                <div class="col s12">
                  <img
                    :src="pathUserLogoSrc"
                    id="currentLogo"
                    style="max-height: 150px"
                    ref="currentLogo"
                  />
                </div>
              </div>

              <Field
                uicontrol="file"
                name="customFavicon"
                :model-value="customFavicon"
                @update:model-value="onFaviconChange($event)"
                :title="translate('CoreAdminHome_FaviconUpload')"
                :inline-help="translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '16')"
              />

              <div class="row">
                <div class="col s12">
                  <img
                    :src="pathUserFaviconSrc"
                    id="currentFavicon"
                    width="16"
                    height="16"
                    ref="currentFavicon"
                  />
                </div>
              </div>
            </div>

            <div v-if="!logosWriteable">
              <div class="alert alert-warning" v-html="$sanitize(logosNotWriteableWarning)"/>
            </div>
          </div>
          <div v-if="!fileUploadEnabled">
            <div class="alert alert-warning">
              {{ translate('CoreAdminHome_FileUploadDisabled', "file_uploads=1") }}
            </div>
          </div>
        </form>
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
  Matomo,
} from 'CoreHome';
import {
  SaveButton,
  Form,
  Field,
} from 'CorePluginsAdmin';

const { $ } = window;

interface BrandingSettingsState {
  isLoading: boolean;
  enabled: boolean;
  customLogo: string|File;
  customFavicon: string|File;
  showUploadError: boolean;
  currentLogoSrcExists: boolean;
  currentFaviconSrcExists: boolean;
  newLogoBase64Src: string;
  newFaviconBase64Src: string;
}

export default defineComponent({
  props: {
    fileUploadEnabled: {
      type: Boolean,
      required: true,
    },
    logosWriteable: {
      type: Boolean,
      required: true,
    },
    useCustomLogo: {
      type: Boolean,
      required: true,
    },
    pathUserLogoDirectory: {
      type: String,
      required: true,
    },
    pathUserLogo: {
      type: String,
      required: true,
    },
    pathUserLogoSmall: {
      type: String,
      required: true,
    },
    pathUserLogoSvg: {
      type: String,
      required: true,
    },
    hasUserLogo: {
      type: Boolean,
      required: true,
    },
    pathUserFavicon: {
      type: String,
      required: true,
    },
    hasUserFavicon: {
      type: Boolean,
      required: true,
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true,
    },
  },
  components: {
    Field,
    ContentBlock,
    SaveButton,
  },
  directives: {
    Form,
  },
  data(): BrandingSettingsState {
    return {
      isLoading: false,
      enabled: this.useCustomLogo,
      customLogo: this.pathUserLogo,
      customFavicon: this.pathUserFavicon,
      showUploadError: false,
      currentLogoSrcExists: this.hasUserLogo,
      currentFaviconSrcExists: this.hasUserFavicon,
      newLogoBase64Src: '',
      newFaviconBase64Src: '',
    };
  },
  computed: {
    tokenAuth() {
      return Matomo.token_auth;
    },
    logosNotWriteableWarning() {
      return translate(
        'CoreAdminHome_LogoNotWriteableInstruction',
        `<code>${this.pathUserLogoDirectory}</code><br/>`,
        `${this.pathUserLogo}, ${this.pathUserLogoSmall}, ${this.pathUserLogoSvg}`,
      );
    },
    help() {
      if (!this.isPluginsAdminEnabled) {
        return undefined;
      }

      const giveUsFeedbackText = `"${translate('General_GiveUsYourFeedback')}"`;
      const linkStart = '<a href="?module=CorePluginsAdmin&action=plugins" '
        + 'rel="noreferrer noopener" target="_blank">';
      return translate(
        'CoreAdminHome_CustomLogoFeedbackInfo',
        giveUsFeedbackText,
        linkStart,
        '</a>',
      );
    },
    pathUserLogoSrc() {
      if (this.newLogoBase64Src) {
        return `data:image/png;base64, ${this.newLogoBase64Src}`;
      }
      if (this.currentLogoSrcExists && this.pathUserLogo) {
        return this.pathUserLogo;
      }

      return '';
    },
    pathUserFaviconSrc() {
      if (this.newFaviconBase64Src) {
        return `data:image/png;base64, ${this.newFaviconBase64Src}`;
      }

      if (this.currentFaviconSrcExists && this.pathUserFavicon) {
        return this.pathUserFavicon;
      }

      return '';
    },
  },
  methods: {
    onUseCustomLogoChange(newValue: boolean) {
      this.enabled = newValue;
    },
    onCustomLogoChange(newValue: File) {
      this.customLogo = newValue;
      this.updateLogo();
    },
    onFaviconChange(newValue: File) {
      this.customFavicon = newValue;
      this.updateLogo();
    },
    save() {
      this.isLoading = true;
      AjaxHelper.post({
        module: 'API',
        method: 'CoreAdminHome.setBrandingSettings',
      },
      {
        useCustomLogo: this.enabled ? '1' : '0',
        hasCustomLogo: (this.newLogoBase64Src.length > 0 || this.customLogo) ? '1' : '0',
        hasCustomFavicon: (this.newFaviconBase64Src.length > 0 || this.customFavicon) ? '1' : '0',
      }).then((response) => {
        this.enabled = !!response.useCustomLogo;
        if (response.customLogoPath) {
          this.customLogo = response.customLogoPath;
        }
        if (response.customFaviconPath) {
          this.customFavicon = response.customFaviconPath;
        }

        const notificationInstanceId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).finally(() => {
        if (!this.enabled) {
          // reset fields so that values are not visible when setting is enabled without page reload
          this.currentLogoSrcExists = false;
          this.currentFaviconSrcExists = false;
          this.customLogo = '';
          this.customFavicon = '';
          this.newFaviconBase64Src = '';
          this.newLogoBase64Src = '';
        }
        this.isLoading = false;
      });
    },
    updateLogo() {
      const isSubmittingLogo = !!this.customLogo;
      const isSubmittingFavicon = !!this.customFavicon;

      if (!isSubmittingLogo && !isSubmittingFavicon) {
        return;
      }

      this.showUploadError = false;

      const frameName = `upload${(new Date()).getTime()}`;
      const uploadFrame = $(`<iframe name="${frameName}" />`);
      uploadFrame.css('display', 'none');
      uploadFrame.on('load', () => {
        setTimeout(() => {
          let frameContent = '';
          let frameContentJSON: Record<string, string> = {};
          try {
            frameContent = ($(uploadFrame.contents()).find('body').text() || '').trim();
            frameContentJSON = JSON.parse(frameContent as string) as Record<string, string>;
          } catch (e) {
            // invalid JSON
          }

          if (frameContent && Object.keys(frameContentJSON).length === 0) {
            this.showUploadError = true;
          } else {
            // Upload succeed, so we update the images availability
            // according to what have been uploaded
            if (isSubmittingLogo && frameContentJSON.logo) {
              this.newLogoBase64Src = frameContentJSON.logo;
            }
            if (isSubmittingFavicon && frameContentJSON.favicon) {
              this.newFaviconBase64Src = frameContentJSON.favicon;
            }
          }

          if (frameContent) {
            uploadFrame.remove();
          }
        }, 1000);
      });
      $('body:first').append(uploadFrame);

      const submittingForm = $(this.$refs.logoUploadForm as HTMLElement);
      submittingForm.attr('target', frameName);
      submittingForm.submit();

      this.customLogo = '';
      this.customFavicon = '';
    },
  },
});
</script>
