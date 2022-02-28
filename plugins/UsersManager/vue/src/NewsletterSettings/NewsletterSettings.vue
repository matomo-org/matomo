<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div id="newsletterSignup"
    v-show="showNewsletterSignup"
  >
    <ContentBlock
       :content-title="translate('UsersManager_NewsletterSignupTitle')"
    >
      <div>
        <Field
          uicontrol="checkbox"
          name="newsletterSignupCheckbox"
          id="newsletterSignupCheckbox"
          v-model="newsletterSignupCheckbox"
          :full-width="true"
          :title="signupTitleText"
        />
      </div>

      <SaveButton
        id="newsletterSignupBtn"
        @confirm="signupForNewsletter()"
        :disabled="!newsletterSignupCheckbox"
        :value="newsletterSignupButtonTitle"
        :saving="isProcessingNewsletterSignup"
      />
    </ContentBlock>
  </div>

</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  ContentBlock,
  translate,
  NotificationsStore,
} from 'CoreHome';
import { SaveButton, Field } from 'CorePluginsAdmin';

interface NewsletterSettingsState {
  showNewsletterSignup: boolean;
  newsletterSignupCheckbox: boolean;
  isProcessingNewsletterSignup: boolean;
  newsletterSignupButtonTitle: string;
}

export default defineComponent({
  data(): NewsletterSettingsState {
    return {
      showNewsletterSignup: true,
      newsletterSignupCheckbox: false,
      isProcessingNewsletterSignup: false,
      newsletterSignupButtonTitle: translate('General_Save'),
    };
  },
  components: {
    ContentBlock,
    SaveButton,
    Field,
  },
  computed: {
    signupTitleText() {
      return translate(
        'UsersManager_NewsletterSignupMessage',
        '<a href="https://matomo.org/privacy-policy/" target="_blank">',
        '</a>',
      );
    },
  },
  methods: {
    signupForNewsletter() {
      this.newsletterSignupButtonTitle = translate('General_Loading');

      this.isProcessingNewsletterSignup = true;

      AjaxHelper.fetch(
        {
          module: 'API',
          method: 'UsersManager.newsletterSignup',
        },
        { withTokenInUrl: true },
      ).then(() => {
        this.isProcessingNewsletterSignup = false;
        this.showNewsletterSignup = false;

        const id = NotificationsStore.show({
          message: translate('UsersManager_NewsletterSignupSuccessMessage'),
          id: 'newslettersignup',
          context: 'success',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(id);
      }).catch(() => {
        this.isProcessingNewsletterSignup = false;

        const id = NotificationsStore.show({
          message: translate('UsersManager_NewsletterSignupFailureMessage'),
          id: 'newslettersignup',
          context: 'error',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(id);

        this.newsletterSignupButtonTitle = translate('General_PleaseTryAgain');
      });
    },
  },
});
</script>
