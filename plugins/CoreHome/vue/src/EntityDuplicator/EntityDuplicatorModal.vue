<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div
    :class="{
      'modal': true,
      'entity-duplicator-modal': true,
      'slot-configured': $slots.default
    }"
    ref="root">
    <div class="main-duplicator-modal-content" v-show="isModalVisible">
      <div class="modal-header">
        <span class="btn-close modal-close"><i class="icon-close"></i></span>
        <h2>
          {{ getModalTitle }}
        </h2>
      </div>

      <template v-if="isLoading">
        <div class="modal-sub-header">
          <MatomoLoader />
          <span class="loading-message">{{ translate('General_Loading') }}</span>
        </div>
      </template>

      <template v-else>
        <div class="modal-sub-header" v-if="!hideSiteSelector">
          <p>
            {{ getDuplicateDescription }}
            <span v-if="descriptionLearnMoreLink" v-html="$sanitize(getLearnMoreLink)"></span>
          </p>
          <Field
            uicontrol="site"
            name="siteSelector"
            :title="translate('CoreHome_ChooseWebsite')"
            v-model="destinationSite"
            :ui-control-attributes="{
              onlySitesWithAtLeastWriteAccess: true,
              siteTypesToExclude: ['rollup'],
            }"
          />
        </div>
        <div class="modal-content">
          <div v-form class="modal-inputs">
            <slot></slot>
          </div>
        </div>
        <div class="modal-sub-footer">
          <div
            :class="{
              'alert': true,
              'alert-danger': true,
              'error-list': duplicationErrors.length > 1
            }"
            v-if="duplicationErrors.length > 0">
            <ul>
              <li
                v-for="(duplicationError, index) in duplicationErrors"
                :key="index"
                v-html="$sanitize(duplicationError)"
              />
            </ul>
          </div>
          <p class="note-text" v-html="$sanitize(getNoteText)"/>
        </div>
        <div class="modal-footer">
          <MatomoLoader v-show="hasBeenSubmitted" />
          <button
            class="btn"
            :disabled="!getIsValid || hasBeenSubmitted"
            @click="submitRequest()"
          >{{ translate('General_Copy') }}</button>
        </div>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  watch,
  PropType,
} from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';
import SiteRef from '../SiteSelector/SiteRef';
import { translate } from '../translate';
import { externalLink } from '../externalLink';
import { EntityDuplicatorStore } from './EntityDuplicatorStore';
import {
  DuplicateRequestResponse,
  ValidationResult,
} from './EntityDuplicatorAdapter';
import MatomoLoader from '../MatomoLoader/MatomoLoader';

// async since we're referencing a recursive component
const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
const Form = useExternalPluginComponent('CorePluginsAdmin', 'Form');

const { $ } = window;

interface EntityDuplicatorState {
  isLoading: boolean;
  isValidated: boolean;
  duplicationErrors: string[];
  destinationSite: SiteRef|null;
  hasBeenSubmitted: boolean;
}

export default defineComponent({
  directives: {
    Form,
  },
  components: {
    MatomoLoader,
    Field,
  },
  props: {
    /**
     * The reactive class for controlling the settings of the modal from multiple components.
     */
    modalStore: {
      type: Object as PropType<EntityDuplicatorStore>,
      required: true,
    },
    /**
     * Option to hide the site selector when it's not needed.
     */
    hideSiteSelector: {
      type: Boolean,
      default: false,
    },
    /**
     * Optional "Learn more." link to append to the end of the description text if provided.
     */
    descriptionLearnMoreLink: {
      type: String,
      default: '',
    },
  },
  data(): EntityDuplicatorState {
    return {
      isLoading: true,
      isValidated: false,
      duplicationErrors: [],
      destinationSite: null,
      hasBeenSubmitted: false,
    };
  },
  watch: {
    isModalVisible(newValue) {
      if (!newValue) {
        return;
      }

      // Call adapter's beforeShowModal if defined
      let beforeShowModal: void | Promise<void>;
      if (this.modalStore.adapter.beforeShowModal) {
        beforeShowModal = this.modalStore.adapter.beforeShowModal();
      }

      // If a promise was returned, leave as loading until the promise is resolved
      if (!beforeShowModal || typeof beforeShowModal === 'undefined') {
        beforeShowModal = new Promise<void>((resolve) => resolve());
      }

      this.showModal();

      // If a promise was returned, use that to set the state at the right time
      beforeShowModal.then(() => { this.isLoading = false; });
    },
    destinationSite() {
      // Reset flag since the data has changed since validation
      this.isValidated = false;
    },
  },
  methods: {
    closeModal() {
      const root = this.$refs.root as HTMLElement;
      const $root = $(root);
      $root.modal('close');
    },
    resetModal() {
      this.modalStore.hideModal();
      this.destinationSite = null;
      this.isLoading = true;
      this.isValidated = false;
      this.duplicationErrors = [];
      this.hasBeenSubmitted = false;
    },
    showModal() {
      const root = this.$refs.root as HTMLElement;
      const $root = $(root);
      $root.modal({
        dismissible: true,
        onCloseEnd: () => {
          this.resetModal();
        },
      }).modal('open');
    },
    submitRequest() {
      this.hasBeenSubmitted = true;

      // Make sure the validation passes before making the server request
      this.getValidationResultPromise().then((validationResult: ValidationResult) => {
        if (!validationResult.isValid && validationResult.errorMessages.length > 0) {
          this.isValidated = true;
          this.hasBeenSubmitted = false;
          this.duplicationErrors = validationResult.errorMessages;
          return;
        }

        // Use adapter to prepare API parameters
        const params = this.modalStore.adapter.prepareApiParams(
          this.modalStore.getFormValues(this.destinationSite?.id),
        );

        // Use adapter to submit the request
        this.modalStore.adapter.submitRequest(params).then((response: DuplicateRequestResponse) => {
          if (!response || !response.success) {
            this.setErrorMessages(response);
            return;
          }

          // Call adapter's onSuccess if defined
          if (this.modalStore.adapter.onSuccess) {
            this.modalStore.adapter.onSuccess(response);
          }

          this.closeModal();
        }).catch((error) => {
          this.setErrorMessages();
          // Call adapter's onFailure if defined
          if (this.modalStore.adapter.onFailure) {
            this.modalStore.adapter.onFailure(error);
          }

          console.log('Unexpected server error during request.', error);
        }).finally(() => {
          this.hasBeenSubmitted = false;
        });
      });
    },
    getValidationResultPromise(): Promise<ValidationResult> {
      this.duplicationErrors = [];

      // Use adapter for validation
      const validationResultPromise = this.modalStore.adapter.validateFormFields(
        this.modalStore.getFormValues(this.destinationSite?.id),
      );
      // If a promise wasn't returned wrap the result with a promise for consistent processing
      return 'isValid' in validationResultPromise
        ? new Promise((resolve) => resolve(validationResultPromise))
        : validationResultPromise;
    },
    setErrorMessages(response: null|DuplicateRequestResponse = null) {
      let message = response?.message || '';

      // If the error message wasn't set, set it to a generic error message
      if (!message || message.length === 0) {
        message = translate('General_ErrorRequest', '', '');
      }

      this.duplicationErrors = [];
      this.duplicationErrors.push(message);
    },
  },
  mounted() {
    // Watch the formData object for any property changes to know whether current data was validated
    watch(
      () => this.modalStore.state.entityFormData,
      () => {
        this.isValidated = false;
      },
      { deep: true },
    );
  },
  computed: {
    isModalVisible(): boolean {
      return this.modalStore.state.isModalVisible ?? false;
    },
    getModalTitle(): string {
      return translate('CoreHome_CopyX', this.modalStore.getEntityTypeTranslation);
    },
    getNoteText(): string {
      const noteText = translate(
        'CoreHome_CopyModalNote',
        '<strong>',
        '</strong>',
        this.modalStore.getEntityTypeTranslation,
      );

      return `${noteText}`;
    },
    getDuplicateDescription(): string {
      return translate('CoreHome_CopyXDescription', this.modalStore.getEntityTypeTranslation);
    },
    getLearnMoreLink() {
      if (!this.descriptionLearnMoreLink) {
        return '';
      }

      const linkString = externalLink(this.descriptionLearnMoreLink);
      return translate('CoreHome_LearnMoreFullStop', linkString, '</a>');
    },
    getIsValid(): boolean {
      // Show as valid until validation has actually been checked
      if (!this.isValidated) {
        return true;
      }

      return Array.isArray(this.duplicationErrors) && this.duplicationErrors.length === 0;
    },
  },
});
</script>
