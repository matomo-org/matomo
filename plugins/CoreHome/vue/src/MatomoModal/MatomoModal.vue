<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="modal-overlay open"
      @click="onOverlayClick"
    />
    <div
      v-show="modelValue"
      ref="root"
      class="modal"
      :class="modalClasses"
      role="dialog"
      aria-modal="true"
      :aria-label="ariaLabel"
      tabindex="-1"
    >
      <div class="modal-content" :class="contentClass">
        <slot></slot>
      </div>
      <div v-if="$slots.footer" class="modal-footer">
        <slot name="footer"></slot>
      </div>
    </div>
  </Teleport>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';

type ClassValue = string | string[] | Record<string, boolean>;

export default defineComponent({
  name: 'MatomoModal',
  props: {
    modelValue: {
      type: Boolean,
      required: true,
    },
    // When false, Esc and overlay clicks no longer close the modal. Equivalent
    // to Materialize.js's `dismissible: false`.
    dismissible: {
      type: Boolean,
      default: true,
    },
    // Adds the `.modal-fixed-footer` Materialize variant.
    fixedFooter: {
      type: Boolean,
      default: false,
    },
    // Adds the `.modal-extra-wide` Materialize variant.
    extraWide: {
      type: Boolean,
      default: false,
    },
    // Extra classes applied to the modal root, in the same shape Vue accepts
    // for `:class`. Use this to opt into modal-specific styling.
    classes: {
      type: [String, Array, Object] as PropType<ClassValue>,
      default: '',
    },
    // Extra classes applied to the inner `.modal-content` wrapper.
    contentClass: {
      type: [String, Array, Object] as PropType<ClassValue>,
      default: '',
    },
    ariaLabel: {
      type: String,
      default: undefined,
    },
  },
  emits: ['update:modelValue', 'opened', 'closed'],
  data() {
    return {
      previousBodyOverflow: '',
    };
  },
  computed: {
    modalClasses(): Array<ClassValue> {
      return [
        { open: this.modelValue },
        this.fixedFooter ? 'modal-fixed-footer' : '',
        this.extraWide ? 'modal-extra-wide' : '',
        this.classes,
      ];
    },
  },
  methods: {
    close() {
      if (!this.modelValue) {
        return;
      }
      this.$emit('update:modelValue', false);
    },

    onOverlayClick() {
      if (this.dismissible) {
        this.close();
      }
    },

    onKeydown(event: KeyboardEvent) {
      if (event.key === 'Escape' && this.dismissible) {
        this.close();
      }
    },

    activate() {
      this.previousBodyOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', this.onKeydown);
      this.$nextTick(() => {
        this.$emit('opened', this.$refs.root as HTMLElement);
      });
    },

    deactivate() {
      document.body.style.overflow = this.previousBodyOverflow;
      this.previousBodyOverflow = '';
      document.removeEventListener('keydown', this.onKeydown);
      this.$emit('closed');
    },
  },
  watch: {
    modelValue(open: boolean, wasOpen: boolean) {
      if (open && !wasOpen) {
        this.activate();
      } else if (!open && wasOpen) {
        this.deactivate();
      }
    },
  },
  mounted() {
    if (this.modelValue) {
      this.activate();
    }
  },
  unmounted() {
    if (this.modelValue) {
      document.body.style.overflow = this.previousBodyOverflow;
      document.removeEventListener('keydown', this.onKeydown);
    }
  },
});
</script>
