<template>
  <div
    v-for="setting in settings"
    :key="`${groupPrefix}${setting.name}`"
  >
    <GroupedSetting
      :model-value="allSettingValues[`${groupPrefix}${setting.name}`]"
      @update:model-value="$emit('change', { name: setting.name, value: $event })"
      :setting="setting"
      :condition-values="settingValues"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import GroupedSetting from './GroupedSetting.vue';

export default defineComponent({
  props: {
    groupName: String,
    settings: {
      type: Array,
      required: true,
    },
    allSettingValues: {
      type: Object,
      required: true,
    },
  },
  emits: ['change'],
  components: {
    GroupedSetting,
  },
  computed: {
    settingValues() {
      const entries = Object.entries(this.allSettingValues as Record<string, unknown>)
        .filter(([key]) => {
          if (this.groupName) {
            const [groupName] = key.split('.');
            if (groupName !== this.groupName) {
              return false;
            }
          }

          return true;
        })
        .map(([key, value]) => (this.groupName ? [key.split('.')[1], value] : [key, value]));
      return Object.fromEntries(entries);
    },
    groupPrefix() {
      if (!this.groupName) {
        return '';
      }
      return `${this.groupName}.`;
    },
  },
});
</script>
