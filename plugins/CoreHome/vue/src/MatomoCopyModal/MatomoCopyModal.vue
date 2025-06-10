<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div class="modal matomo-copy-modal" ref="root">
    <div class="entire-copy-modal">
      <div class="modal-header">
        <span class="btn-close modal-close"><i class="icon-close"></i></span>
        <h2>
          {{ getModalTitle }}
        </h2>
      </div>

      <template v-if="isLoading">
        <div class="modal-content copy-loading">
          <div class="Piwik_Popover_Loading">
            <div class="Piwik_Popover_Loading_Name">
              <h2>{{ translate('General_Loading') }}</h2>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <div class="modal-sub-header">
          <p>
            {{ getCopyDescription }}&nbsp;
            <span v-if="descriptionLearnMoreLink" v-html="$sanitize(getLearnMoreLink)"></span>
          </p>
          <Field
            uicontrol="site"
            name="siteSelector"
            :title="translate('CoreHome_ChooseWebsite')"
            v-model="site"
            :ui-control-attributes="{
              sitesWithAtLeastWriteAccess: true,
              excludeRollUpSites: true,
            }"
          />
        </div>
        <div :class="$slots.default ? 'modal-content copy-configure' : 'modal-content'">
          <div v-form class="modal-inputs">
            <slot></slot>
          </div>
        </div>
        <div class="modal-sub-footer">
          <div :class="getAlertClasses" v-if="copyErrors.length > 0">
            <ul>
              <li
                v-for="(copyError, index) in copyErrors"
                :key="index"
                v-html="$sanitize(copyError)"
              />
            </ul>
          </div>
          <p class="note-text"
             v-html="$sanitize(getNoteText)"
             v-if="copyErrors.length === 0"
          />
        </div>
        <div class="modal-footer">
          <button
            class="btn"
            :disabled="!getIsValid || hasBeenSubmitted"
            @click="submitCopy()"
          >{{ translate('General_Copy') }}</button>
        </div>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent, watch,
} from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';
import SiteRef from '../SiteSelector/SiteRef';
import Matomo from '../Matomo/Matomo';
import debounce from '../debounce';
import { translate, translateOrDefault } from '../translate';
import { externalLink } from '../externalLink';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

// async since we're referencing a recursive component
const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
const Form = useExternalPluginComponent('CorePluginsAdmin', 'Form');

const { $ } = window;

interface MatomoCopyModalState {
  isLoading: boolean;
  isValidated: boolean;
  descriptionLearnMoreLink: string;
  copyErrors: string[];
  site: SiteRef|null;
  hasSiteBeenInitialised: boolean;
  hasBeenSubmitted: boolean;
}

interface CopyRequestResponse {
  isCopySuccessful?: boolean;
  successMessage?: string;
  responseData?: Record<string, unknown>;
  errorMessage?: string;
  errorCode?: number;
}

export default defineComponent({
  directives: {
    Form,
  },
  components: {
    Field,
  },
  props: {
    /**
     * Whether the modal is displayed or not
     */
    modelValue: {
      type: Boolean,
      required: true,
      default: false,
    },
    copyEntityType: {
      type: String,
      required: true,
      default: '',
    },
    copyEntityTypeTranslation: {
      type: String,
      required: false,
      default: '',
    },
    formData: {
      type: Object,
      required: false,
      default: () => ({}),
    },
  },
  data(): MatomoCopyModalState {
    return {
      isLoading: true,
      isValidated: false,
      descriptionLearnMoreLink: '',
      copyErrors: [],
      site: null,
      hasSiteBeenInitialised: false,
      hasBeenSubmitted: false,
    };
  },
  emits: [
    'update:modelValue',
    'resetFormData',
    'copySuccessful',
    'copyFailed',
  ],
  watch: {
    modelValue(newValue) {
      if (!newValue) {
        return;
      }

      // TODO - Do some logic before showing modal

      this.showCopyModal();

      // TODO - determine the best indication that loading is done
      this.isLoading = false;
    },
    site() {
      this.onSiteChange();
    },
  },
  methods: {
    closeModal() {
      const root = this.$refs.root as HTMLElement;
      const $root = $(root);
      $root.modal('close');
    },
    resetModal() {
      this.site = null;
      this.isLoading = true;
      this.isValidated = false;
      this.copyErrors = [];
      this.hasSiteBeenInitialised = false;
      this.hasBeenSubmitted = false;
      this.$emit('resetFormData');
    },
    showCopyModal() {
      const root = this.$refs.root as HTMLElement;
      const $root = $(root);
      $root.modal({
        dismissible: true,
        onCloseEnd: () => {
          this.resetModal();
          this.$emit('update:modelValue', false);
        },
      }).modal('open');
    },
    submitCopy() {
      this.hasBeenSubmitted = true;
      // It should have already run in order for the copy button to be enabled, but let's confirm
      this.validateFormFields();
      if (!this.getIsValid) {
        return;
      }

      // Actually POST the API call
      const ajax = new AjaxHelper();
      // Remove the unnecessary default parameters
      ajax.removeDefaultParameter('date');
      ajax.removeDefaultParameter('period');
      ajax.removeDefaultParameter('segment');
      // Include token in POST body so that it can be used for the security check instead of a nonce
      ajax.withTokenInUrl();
      ajax.addParams({
        module: 'CoreHome',
        action: 'copyEntity',
        idSite: Matomo.idSite || MatomoUrl.parsed.value.idSite,
        idDestinationSites: [this.site?.id],
        entityTypeName: this.copyEntityType,
        ...this.formData,
      }, 'POST');
      ajax.setFormat('json');
      ajax.send().then((response: CopyRequestResponse) => {
        // If the response was invalid or unsuccessful, emit the failure and show an error message
        if (!response || !response.isCopySuccessful) {
          this.emitFailureAndSetErrorMessage();
          return;
        }

        // Emit success so parent can perform desired actions like reload the data store or page
        this.$emit('copySuccessful', response);

        this.closeModal();
      }).catch((error) => {
        this.emitFailureAndSetErrorMessage();
        console.log('Unexpected server error during request.', error);
      }).finally(() => {
        this.hasBeenSubmitted = false;
      });
    },
    validateFormFields() {
      this.isValidated = true;
      this.copyErrors = [];
      // Don't bother if the modal isn't visible
      if (!this.modelValue) {
        return;
      }

      // Ignore the site getting initialised by the component
      if (!this.hasSiteBeenInitialised) {
        this.hasSiteBeenInitialised = true;
        return;
      }

      const validationData: QueryParameters = {
        formValues: {
          ...this.formData,
          idDestinationSite: this.site?.id,
        },
        errorMessages: [] as string[],
      };
      Matomo.postEvent('MatomoCopyModal:validateFormFields', validationData);
      if (
        validationData
        && Array.isArray(validationData.errorMessages)
        && validationData.errorMessages.length > 0
      ) {
        this.copyErrors = validationData.errorMessages;
      }
    },
    validateAfterFieldChange() {
      this.validateFormFields();
      this.hasBeenSubmitted = false;
    },
    onSiteChange() {
      this.validateAfterFieldChange();
    },
    emitFailureAndSetErrorMessage(response: null|CopyRequestResponse = null) {
      let tempResponseObject = response;
      // If no response object is set, create one with a generic error message
      if (!tempResponseObject) {
        tempResponseObject = {
          isCopySuccessful: false,
          errorMessage: translate('General_ErrorRequest'),
        };
      }

      // If the error message wasn't set, set it to a generic error message
      if (!tempResponseObject.errorMessage || tempResponseObject.errorMessage.length === 0) {
        tempResponseObject.errorMessage = translate('General_ErrorRequest');
      }

      this.copyErrors = [];
      this.copyErrors.push(tempResponseObject.errorMessage);
      this.$emit('copyFailed', tempResponseObject);
    },
  },
  mounted() {
    // Add a delay to validation to try and let the input finish
    const delayedValidation = debounce(this.validateAfterFieldChange);

    // Watch the formData object for any property changes
    watch(
      () => this.formData,
      () => {
        delayedValidation();
      },
      { deep: true },
    );
  },
  computed: {
    getModalTitle(): string {
      return translate('CoreHome_CopyX', this.getEntityTypeTranslation);
    },
    getEntityTypeTranslation(): string {
      let translationKey = 'CoreHome_ReportLowercase';
      if (this.copyEntityTypeTranslation) {
        translationKey = this.copyEntityTypeTranslation;
      }

      // Only translate if it's a translation key and not an already translated string
      return translateOrDefault(translationKey);
    },
    getNoteText(): string {
      const noteText = translate(
        'CoreHome_CopyModalNote',
        '<strong>',
        '</strong>',
        this.getEntityTypeTranslation,
      );

      return `${noteText}`;
    },
    getCopyDescription(): string {
      return translate('CoreHome_CopyXDescription', this.getEntityTypeTranslation);
    },
    getLearnMoreLink() {
      if (!this.descriptionLearnMoreLink) {
        return '';
      }

      const linkString = externalLink(this.descriptionLearnMoreLink);
      return translate('CoreHome_LearnMoreFullStop', linkString, '</a>');
    },
    getAlertClasses() {
      const listClass = this.copyErrors.length > 1 ? ' error-list' : '';
      return `alert alert-danger${listClass}`;
    },
    getIsValid(): boolean {
      // Show as valid until validation has actually been checked
      if (!this.isValidated) {
        return true;
      }

      return Array.isArray(this.copyErrors) && this.copyErrors.length === 0;
    },
  },
});
</script>
