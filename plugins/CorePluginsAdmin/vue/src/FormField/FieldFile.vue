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
      <input class="file-path validate" :value="filePath" type="text"/>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import AbortableModifiers from './AbortableModifiers';

export default defineComponent({
  props: {
    name: String,
    title: String,
    modelValue: [String, File],
    modelModifiers: Object,
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  watch: {
    modelValue(v: string|File) {
      if (!v || v === '') {
        const fileInputElement = this.$refs.fileInput as HTMLInputElement;
        fileInputElement!.value = '';
      }
    },
  },
  methods: {
    onChange(event: Event) {
      const { files } = event.target as HTMLInputElement;
      if (!files) {
        return;
      }

      const file = files.item(0);
      if (!(this.modelModifiers as AbortableModifiers)?.abortable) {
        this.$emit('update:modelValue', file);
        return;
      }

      const emitEventData = {
        value: file,
        abort() {
          // not supported
        },
      };

      this.$emit('update:modelValue', emitEventData);
    },
  },
  computed: {
    filePath() {
      if (this.modelValue instanceof File) {
        return (this.$refs.fileInput as HTMLInputElement).value;
      }

      return undefined;
    },
  },
});
</script>
