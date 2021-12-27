<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="matomo-save-button" style="display:inline-block;">
    <input
      type="button"
      @click="onConfirm($event)"
      :disabled="saving || disabled"
      class="btn"
      :value="value ? value : translate('General_Save')"
    />
    <ActivityIndicator
      :loading="saving"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ActivityIndicator } from 'CoreHome';

export default defineComponent({
  props: {
    saving: Boolean,
    value: String,
    disabled: Boolean,
  },
  components: {
    ActivityIndicator,
  },
  emits: ['confirm'],
  methods: {
    onConfirm(event: MouseEvent) {
      this.$emit('confirm', event);
    },
  },
});
</script>
