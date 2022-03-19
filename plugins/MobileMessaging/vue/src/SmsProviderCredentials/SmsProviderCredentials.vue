<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- conversion check (mistakes get fixed in quickmigrate)
- property types
- state types
- look over template
- look over component code
- get to build
- test in UI
- check uses:
  ./plugins/MobileMessaging/templates/macros.twig
  ./plugins/MobileMessaging/MobileMessaging.php
  ./plugins/MobileMessaging/angularjs/sms-provider-credentials.directive.js
  ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue
- create PR
</todo>

<template>
  <div v-if="fields">
    <GroupedSettings
      :settings="fields"
      :all-setting-values="modelValue"
      @change="$emit('update:modelValue', { ...modelValue, [$event.name]: $event.value })"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { AjaxHelper } from 'CoreHome';
import { GroupedSettings } from 'CorePluginsAdmin';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const allFieldsByProvider: Record<string, any[]> = {};

export default defineComponent({
  props: {
    provider: {
      type: String,
      required: true,
    },
    modelValue: {
      type: Object,
      required: true,
    },
  },
  emits: ['update:modelValue'],
  components: {
    GroupedSettings,
  },
  watch: {
    provider() {
      // unset credentials when new provider is chosen
      this.$emit('update:modelValue', [{}]);

      // fetch fields for provider
      this.getCredentialFields();
    },
  },
  created() {
    this.getCredentialFields();
  },
  methods: {
    getCredentialFields() {
      if (allFieldsByProvider[this.provider]) {
        return;
      }

      AjaxHelper.fetch({
        module: 'MobileMessaging',
        action: 'getCredentialFields',
        provider: this.provider,
      }).then((fields) => {
        this.fields = fields;
      });
    },
  },
  computed: {
    fields() {
      return allFieldsByProvider[this.provider];
    },
  },
});
</script>
