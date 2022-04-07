<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root">
    <slot
      :form-data="formData"
      :submit-api-method="submitApiMethod"
      :send-json-payload="sendJsonPayload"
      :no-error-notification="noErrorNotification"
      :no-success-notification="noSuccessNotification"
      :submit-form="submitForm"
      :is-submitting="isSubmitting"
      :successful-post-response="successfulPostResponse"
      :error-post-response="errorPostResponse"
    ></slot>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from '../translate';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import { NotificationsStore } from '../Notification';

const { $ } = window;

/**
 * Example usage:
 *
 * <AjaxForm :form-data="myData" ...>
 *   <template v-slot:default="ajaxForm">
 *     <Field v-model="myData.something" .../>
 *     <SaveButton @confirm="ajaxForm.submitForm()" :saving="ajaxForm.isSubmitting"/>
 *   </template>
 * </AjaxForm>
 *
 * Data does not flow upwards in any way. :form-data is used for submitForm(), and the
 * containing component binds to properties of the object in controls to fill the object.
 */
export default defineComponent({
  props: {
    formData: {
      type: Object,
      required: true,
    },
    submitApiMethod: {
      type: String,
      required: true,
    },
    sendJsonPayload: Boolean,
    noErrorNotification: Boolean,
    noSuccessNotification: Boolean,
  },
  data() {
    return {
      isSubmitting: false,
      successfulPostResponse: null,
      errorPostResponse: null,
    };
  },
  emits: ['update:modelValue'],
  mounted() {
    // on submit call controller submit method
    $(this.$refs.root as HTMLElement).on('click', 'input[type=submit]', () => {
      this.submitForm();
    });
  },
  methods: {
    submitForm() {
      this.successfulPostResponse = null;
      this.errorPostResponse = null;

      let postParams = this.formData;
      if (this.sendJsonPayload) {
        postParams = { data: JSON.stringify(this.formData) };
      }

      this.isSubmitting = true;
      AjaxHelper.post(
        {
          module: 'API',
          method: this.submitApiMethod,
        },
        postParams,
        {
          createErrorNotification: !this.noErrorNotification,
        },
      ).then((response) => {
        this.successfulPostResponse = response;

        if (!this.noSuccessNotification) {
          const notificationInstanceId = NotificationsStore.show({
            message: translate('General_YourChangesHaveBeenSaved'),
            context: 'success',
            type: 'toast',
            id: 'ajaxHelper',
          });
          NotificationsStore.scrollToNotification(notificationInstanceId);
        }
      }).catch((error) => {
        this.errorPostResponse = error.message;
      }).finally(() => {
        this.isSubmitting = false;
      });
    },
  },
});
</script>
