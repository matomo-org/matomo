<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="confirm-password-modal modal" ref="root">
    <div class="modal-content">
      <div class="modal-text">
        <div ref="content"><slot></slot></div>
        <h2 v-if="!requiresPasswordConfirmation && !slotHasContent">
          {{ translate('UsersManager_ConfirmThisChange') }}
        </h2>
        <h2 v-if="requiresPasswordConfirmation && !slotHasContent">
          {{ translate('UsersManager_ConfirmWithReAuthentication') }}
        </h2>
        <div v-if="requiresPasswordConfirmation && slotHasContent">
          {{ translate('UsersManager_ConfirmWithReAuthentication') }}
        </div>
      </div>
      <div v-show="requiresPasswordConfirmation" class="password-confirmation-div">
        <Field
          v-model="passwordConfirmation"
          :uicontrol="'password'"
          :disabled="!requiresPasswordConfirmation ? true : undefined"
          :name="'currentUserPassword'"
          :id="passwordFieldId"
          :autocomplete="'off'"
          :full-width="true"
          :title="translate('UsersManager_YourCurrentPassword')"
          v-auto-clear-password
        >
        </Field>
      </div>
      <div v-if="requireDeleteConfirmation" class="delete-confirmation-div">
        <Field
          v-model="deleteConfirmation"
          :uicontrol="'text'"
          :name="'deleteConfirmation'"
          :id="deleteConfirmationFieldId"
          :autocomplete="'off'"
          :full-width="true"
          :title="deleteConfirmationTitle"
          :ui-control-attributes="{ autocapitalize: 'off' }"
        >
        </Field>
      </div>
    </div>
    <div class="modal-footer">
      <span
        v-if="!!alternativeIdentityConfirmationComponent"
        ref="altIdConfirmation"
        class="confirmPasswordModal__altIdConfirmation"
        :class="{
          'confirmPasswordModal__altIdConfirmation--disabled': deleteConfirmationMissing,
        }"
        :inert="deleteConfirmationMissing ? true : undefined"
      >
        <component
          :is="asComponent(alternativeIdentityConfirmationComponent)"
          @confirmed="onConfirm"
        ></component>
      </span>
      <a
        href=""
        class="modal-action modal-close btn confirm-password-btn"
        :disabled="cannotConfirm ? true : undefined"
        @click="onClickConfirm($event)"
      >{{ translate('General_Confirm') }}</a>
      <a
        href=""
        class="modal-action modal-close modal-no btn-flat"
        @click="onClickCancel($event)"
      >{{ translate('General_Cancel') }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, Component } from 'vue';
import {
  Matomo,
  AutoClearPassword,
  translate,
  useExternalPluginComponent,
} from 'CoreHome';
import Field from '../Field/Field.vue';
import KeyPressEvent = JQuery.KeyPressEvent;

const { $ } = window;

// deliberately not translated: the acknowledgement is the same word in every language, so
// there is only ever one thing to type whichever language the UI is shown in
const DELETE_CONFIRMATION_WORD = 'delete';

interface PluginComponent {
  plugin: string,
  component: string,
}

export interface PasswordConfirmationState {
  passwordConfirmation: string;
  deleteConfirmation: string;
  slotHasContent: boolean;
  altIdConfirmComponent: PluginComponent;
}

export default defineComponent({
  props: {
    /**
     * Whether the confirmation is displayed or not;
     */
    modelValue: {
      type: Boolean,
      required: true,
    },
    passwordFieldId: {
      type: String,
      default: () => 'currentUserPassword',
    },
    /**
     * Whether the user also has to type `delete` before they can confirm. For actions that
     * permanently remove data, where a password alone does not show the impact was understood.
     */
    requireDeleteConfirmation: {
      type: Boolean,
      default: false,
    },
  },
  data(): PasswordConfirmationState {
    return {
      passwordConfirmation: '',
      deleteConfirmation: '',
      slotHasContent: true,
      altIdConfirmComponent: { plugin: '', component: '' },
    };
  },
  emits: ['confirmed', 'aborted', 'update:modelValue'],
  directives: {
    AutoClearPassword,
  },
  components: {
    Field,
  },
  activated() {
    this.$emit('update:modelValue', false);
  },
  methods: {
    // Expose the plugin component to `<component :is>` as a plain Component.
    asComponent(component: unknown): Component {
      return component as Component;
    },
    onClickConfirm(event: MouseEvent) {
      event.preventDefault();

      if (this.cannotConfirm) {
        // the button also carries Materialize's modal-close, which listens on the modal
        // itself - without this it would close the dialog without confirming anything
        event.stopPropagation();
        return;
      }

      this.onConfirm(this.passwordConfirmation);
    },
    onConfirm(passwordConfirmation: string) {
      // only the typed word is checked here. An empty password is a valid confirmation for
      // single sign-on users, whose identity component replaces the password field entirely.
      if (this.deleteConfirmationMissing) {
        return;
      }

      const root = this.$refs.root as HTMLElement;
      const $root = $(root);
      $root.modal('close');
      this.$emit('confirmed', passwordConfirmation);
      this.resetFields();
    },
    onKeyPressConfirm(event: KeyPressEvent) {
      const keycode = event.keyCode ? event.keyCode : event.which;
      if (keycode !== 13) {
        return;
      }

      // the injected component confirms on the user's behalf, so Enter starts it rather
      // than confirming with the empty password sitting behind it
      if (this.alternativeIdentityConfirmationComponent) {
        if (!this.deleteConfirmationMissing) {
          this.clickAlternativeIdentityConfirmation();
        }
        return;
      }

      if (!this.cannotConfirm) {
        this.onConfirm(this.passwordConfirmation);
      }
    },
    clickAlternativeIdentityConfirmation() {
      const wrapper = this.$refs.altIdConfirmation as HTMLElement | undefined;
      const button = wrapper?.querySelector('.btn');
      // LoginSaml marks its button disabled while a re-authentication tab is already open
      if (button instanceof HTMLElement && !button.hasAttribute('disabled')) {
        button.click();
      }
    },
    onClickCancel(event: MouseEvent) {
      event.preventDefault();
      const root = this.$refs.root as HTMLElement;
      const $root = $(root);
      $root.modal('close');
      this.$emit('aborted');
      this.resetFields();
    },
    resetFields() {
      this.passwordConfirmation = '';
      this.deleteConfirmation = '';
    },
    showPasswordConfirmModal() {
      /**
       * Triggered before the confirmation dialog opens, so a plugin can confirm the user's
       * identity in place of the password field.
       *
       * The component renders inside a wrapper the dialog owns, and needs a single `<a>` or
       * `<button>` with the `btn` class - that is what Enter presses, and what is greyed out
       * while the typed delete is missing. Emit `confirmed` with the credential the caller
       * receives instead of a password, and declare it in `emits`.
       *
       * @param object params The plugin and component name to render, empty by default.
       */
      // done here, as the event might not yet have been subscribed in an earlier phase
      Matomo.postEvent('PasswordConfirmation.altIdComponent', this.altIdConfirmComponent);

      this.resetFields();
      this.slotHasContent = !(this.$refs.content as HTMLElement).matches(':empty');
      const root = this.$refs.root as HTMLElement;
      const $root = $(root);

      $root.modal({
        dismissible: false,
        onOpenEnd: () => {
          // the password field is hidden when the install does not ask for a password, so
          // focus the first of the two that is actually on screen
          const fields = $(`.modal.open #${this.deleteConfirmationFieldId}, `
            + `.modal.open #${this.passwordFieldId}`);
          fields.off('keypress').keypress(this.onKeyPressConfirm);
          fields.filter(':visible').first().focus();
        },
        onCloseEnd: () => {
          this.$emit('update:modelValue', false);
        },
      }).modal('open');
    },
  },
  computed: {
    requiresPasswordConfirmation() {
      return !!Matomo.requiresPasswordConfirmation;
    },
    deleteConfirmationTitle() {
      // emphasised rather than quoted, so nobody wonders whether the quotes are part of it
      return translate(
        'CorePluginsAdmin_TypeWordToConfirm',
        `<strong>${DELETE_CONFIRMATION_WORD}</strong>`,
      );
    },
    deleteConfirmationFieldId() {
      return `${this.passwordFieldId}DeleteConfirmation`;
    },
    deleteConfirmationMissing() {
      return this.requireDeleteConfirmation
        && this.deleteConfirmation !== DELETE_CONFIRMATION_WORD;
    },
    // Enter has to mean exactly what clicking Confirm means, so both read the same condition
    cannotConfirm() {
      return this.deleteConfirmationMissing
        || (this.requiresPasswordConfirmation && !this.passwordConfirmation);
    },
    alternativeIdentityConfirmationComponent() {
      if (this.altIdConfirmComponent.plugin && this.altIdConfirmComponent.component) {
        return useExternalPluginComponent(
          this.altIdConfirmComponent.plugin,
          this.altIdConfirmComponent.component,
        );
      }

      return null;
    },
  },
  watch: {
    modelValue(newValue) {
      if (newValue) {
        this.showPasswordConfirmModal();
      }
    },
  },
});

</script>
