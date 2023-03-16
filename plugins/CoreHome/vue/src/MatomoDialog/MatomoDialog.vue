<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <div v-show="modelValue" ref="root">
    <slot></slot>
  </div>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import Matomo from '../Matomo/Matomo';

export default defineComponent({
  props: {
    /**
     * Whether the modal is displayed or not;
     */
    modelValue: {
      type: Boolean,
      required: true,
    },

    /**
     * Only here for backwards compatibility w/ AngularJS. If supplied, we use this
     * element to launch the modal instead of the element in the slot. This should not
     * be used for new Vue code.
     *
     * @deprecated
     */
    element: {
      type: HTMLElement,
      required: false,
    },
  },
  emits: ['yes', 'no', 'closeEnd', 'close', 'validation', 'update:modelValue'],
  activated() {
    this.$emit('update:modelValue', false);
  },
  watch: {
    modelValue(newValue, oldValue) {
      if (newValue) {
        const slotElement = this.element
          || (this.$refs.root as HTMLElement).firstElementChild as HTMLElement;
        Matomo.helper.modalConfirm(slotElement, {
          yes: () => { this.$emit('yes'); },
          no: () => { this.$emit('no'); },
          validation: () => { this.$emit('validation'); },
        }, {
          onCloseEnd: () => {
            // materialize removes the child element, so we move it back to the slot
            if (!this.element) {
              (this.$refs.root as HTMLElement).appendChild(slotElement);
            }
            this.$emit('update:modelValue', false);
            this.$emit('closeEnd');
          },
        });
      } else if (newValue === false && oldValue === true) {
        // the user closed the dialog, e.g. by pressing Esc or clicking away from it
        $('.modal.open').modal('close');
        this.$emit('close');
      }
    },
  },
});
</script>
