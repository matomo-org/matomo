<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <input
    :placeholder="translate('General_Value')"
    type="text"
    class="autocomplete"
    :title="translate('General_Value')"
    autocomplete="off"
    :value="or.value"
    @keydown="onKeydownOrConditionValue($event)"
    @change="onKeydownOrConditionValue($event)"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { debounce } from 'CoreHome';

export default defineComponent({
  props: {
    or: Object,
  },
  created() {
    this.onKeydownOrConditionValue = debounce(this.onKeydownOrConditionValue, 50);
  },
  emits: ['update'],
  methods: {
    onKeydownOrConditionValue(event: Event) {
      this.$emit('update', (event.target as HTMLInputElement).value);
    },
  },
});
</script>
