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
          {{ translate('UsersManager_ConfirmWithPassword') }}
        </h2>
        <div v-if="requiresPasswordConfirmation && slotHasContent">
          {{ translate('UsersManager_ConfirmWithPassword') }}
        </div>
      </div>
      <div v-show="requiresPasswordConfirmation">
        <Field
          v-model="passwordConfirmation"
          :uicontrol="'password'"
          :disabled="!requiresPasswordConfirmation ? 'disabled' : undefined"
          :name="'currentUserPassword'"
          :autocomplete="'off'"
          :full-width="true"
          :title="translate('UsersManager_YourCurrentPassword')"
        >
        </Field>
      </div>
    </div>
    <div class="modal-footer">
      <a
        href=""
        class="modal-action modal-close btn"
        :disabled="requiresPasswordConfirmation && !passwordConfirmation ? 'disabled' : undefined"
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
import { defineComponent } from 'vue';
import { Matomo } from 'CoreHome';
import Field from '../Field/Field.vue';
import KeyPressEvent = JQuery.KeyPressEvent;

const { $ } = window;

interface PasswordConfirmationState {
  passwordConfirmation: string;
  slotHasContent: boolean;
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
  },
  data(): PasswordConfirmationState {
    return {
      passwordConfirmation: '',
      slotHasContent: true,
    };
  },
  emits: ['confirmed', 'aborted', 'update:modelValue'],
  components: {
    Field,
  },
  activated() {
    this.$emit('update:modelValue', false);
  },
  methods: {
    onClickConfirm(event: MouseEvent) {
      event.preventDefault();
      this.$emit('confirmed', this.passwordConfirmation);
      this.passwordConfirmation = '';
    },
    onClickCancel(event: MouseEvent) {
      event.preventDefault();
      this.$emit('aborted');
      this.passwordConfirmation = '';
    },
    showPasswordConfirmModal() {
      this.slotHasContent = !(this.$refs.content as HTMLElement).matches(':empty');
      const root = this.$refs.root as HTMLElement;
      const $root = $(root);
      const onEnter = (event: KeyPressEvent) => {
        const keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode === 13) {
          $root.modal('close');
          this.$emit('confirmed', this.passwordConfirmation);
          this.passwordConfirmation = '';
        }
      };

      $root.modal({
        dismissible: false,
        onOpenEnd: () => {
          const passwordField = '.modal.open #currentUserPassword';
          $(passwordField).focus();
          $(passwordField).off('keypress').keypress(onEnter);
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
