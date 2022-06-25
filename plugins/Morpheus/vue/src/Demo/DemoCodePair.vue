<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <h2 v-if="snippet.title">{{ snippet.title }}</h2>
    <div class="demo" v-if="snippet.vue_embed">
      <component :is="vueEmbedComponent" />
    </div>
    <div class="demo-code" v-if="snippet.code">
      <pre>{{ processedSnippetCode }}</pre>
    </div>
    <p v-if="snippet.desc">
      {{ snippet.desc }}
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw, Directive } from 'vue';
import { useExternalPluginComponent } from 'CoreHome';

export default defineComponent({
  props: {
    snippet: {
      type: Object,
      required: true,
    },
  },
  computed: {
    vueEmbedComponent() {
      const components: Record<string, ReturnType<typeof useExternalPluginComponent>> = {};
      (this.snippet.components || []).forEach((info) => {
        components[info.component] = useExternalPluginComponent(info.plugin, info.component);
      });

      const directives: Record<string, Directive> = {};
      (this.snippet.directives || []).forEach((info) => {
        directives[info.directive] = window[info.plugin][info.directive];
      });

      const dataToUse: Record<string, unknown> = this.snippet.data || {};

      return markRaw({
        template: this.snippet.vue_embed,
        components,
        directives,
        data() {
          return dataToUse;
        },
      });
    },
    processedSnippetCode() {
      const { snippet } = this;
      const spaces = '  ';
      return snippet.code.replaceAll('%vue_embed%', snippet.vue_embed.replaceAll('\n', `\n${spaces}`));
    },
  },
});
</script>
