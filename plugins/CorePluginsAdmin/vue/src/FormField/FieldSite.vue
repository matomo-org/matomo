<template>
  <div>
    <label :for="name" class="siteSelectorLabel" v-html="$sanitize(title)"></label>
    <div>
      <SiteSelector
        class="sites_autocomplete"
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
import { SiteSelector, SiteReference } from 'CoreHome';

export default defineComponent({
  props: {
    name: String,
    title: String,
    modelValue: Object,
    uiControlAttributes: Object,
  },
  inheritAttrs: false,
  components: {
    SiteSelector,
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(newValue: SiteReference) {
      this.$emit('update:modelValue', newValue);
    },
  },
});
</script>
