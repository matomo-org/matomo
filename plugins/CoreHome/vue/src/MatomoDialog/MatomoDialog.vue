<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <slot></slot>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import Matomo from '../Matomo/Matomo';

export default defineComponent({
  props: {
    /**
     * Whether the modal is displayed or not;
     */
    show: {
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
  emits: ['yes', 'no', 'closeEnd', 'close'],
  activated() {
    const slotElement = this.element || this.$slots.default()[0].el;
    slotElement.style.display = 'none';
  },
  watch: {
    show(newValue, oldValue) {
      if (newValue) {
        const slotElement = this.element || this.$slots.default()[0].el;
        Matomo.helper.modalConfirm(slotElement, {
          yes: () => { this.$emit('yes'); },
          no: () => { this.$emit('no'); },
        }, {
          onCloseEnd: () => { this.$emit('closeEnd'); },
        });
      } else if (newValue === false && oldValue === true) {
        // the user closed the dialog, e.g. by pressing Esc or clicking away from it
        this.$emit('close');
      }
    },
  },
});
</script>
