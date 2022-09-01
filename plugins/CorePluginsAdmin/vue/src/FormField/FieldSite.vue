<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <label :for="name" class="siteSelectorLabel" v-html="$sanitize(title)"></label>
    <div class="sites_autocomplete">
      <SiteSelector
        :model-value="modelValue"
        @update:modelValue="onChange($event)"
        :id="name"
        :show-all-sites-item="uiControlAttributes.showAllSitesItem || false"
        :switch-site-on-select="false"
        :show-selected-site="true"
        :only-sites-with-admin-access="uiControlAttributes.onlySitesWithAdminAccess || false"
        v-bind="uiControlAttributes"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { SiteSelector, SiteRef } from 'CoreHome';
import AbortableModifiers from './AbortableModifiers';

export default defineComponent({
  props: {
    name: String,
    title: String,
    modelValue: Object,
    modelModifiers: Object,
    uiControlAttributes: Object,
  },
  inheritAttrs: false,
  components: {
    SiteSelector,
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(newValue: SiteRef) {
      if (!(this.modelModifiers as AbortableModifiers)?.abortable) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      const emitEventData = {
        value: newValue,
        abort() {
          // empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        },
      };

      this.$emit('update:modelValue', emitEventData);
    },
  },
});
</script>
