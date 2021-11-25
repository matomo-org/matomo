<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div class="btn">
      <span :for="name" v-html="$sanitize(title)"></span>
      <input ref="fileInput" :name="name" type="file" :id="name" @change="onChange($event)" />
    </div>

    <div class="file-path-wrapper">
      <input class="file-path validate" :value="modelValue" type="text"/>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  watch,
  ref,
} from 'vue';

export default defineComponent({
  props: {
    name: String,
    title: String,
    modelValue: String,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  setup(props) {
    const fileInput = ref<HTMLInputElement>(null);

    watch(() => props.modelValue, (v) => {
      if (v === '') {
        const fileInputElement = fileInput.value;
        fileInputElement.value = '';
      }
    });

    return {
      fileInput,
    };
  },
  methods: {
    onChange(event: Event) {
      const file = (event.target as HTMLInputElement).files.item(0);
      this.$emit('update:modelValue', file);
    },
  },
});
</script>
