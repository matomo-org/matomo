<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="confirm-password-modal modal" ref="root">
    <div class="modal-content">
      <div class="modal-text">
        <slot></slot>
      </div>
      <div>
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
        @click="$event.preventDefault();
                $emit('confirmed', passwordConfirmation);
                passwordConfirmation = ''"
      >{{ translate('General_Yes') }}</a>
      <a
        href=""
        class="modal-action modal-close modal-no btn-flat"
        @click="$event.preventDefault(); $emit('aborted')"
      >{{ translate('General_No') }}</a>
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
    showPasswordConfirmModal() {
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
