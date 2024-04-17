<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div :style="{'margin-top': snippet.noMargin ? '-16px' : undefined}">
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

interface Snippet {
  id: string;
  vue_embed?: string;
  code?: string;
  data?: unknown;
  components?: { plugin: string, component: string }[];
  directives?: { plugin: string, directive: string }[];
  noMargin?: boolean;
}

export default defineComponent({
  props: {
    snippet: {
      type: Object,
      required: true,
    },
  },
  computed: {
    vueEmbedComponent() {
      const snippet = this.snippet as Snippet;

      const components: Record<string, ReturnType<typeof useExternalPluginComponent>> = {};
      (snippet.components || []).forEach((info) => {
        components[info.component] = useExternalPluginComponent(info.plugin, info.component);
      });

      const directives: Record<string, Directive> = {};
      (snippet.directives || []).forEach((info) => {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        directives[info.directive] = (window as any)[info.plugin][info.directive];
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
      const vueEmbedIndex = snippet.code.indexOf('%vue_embed%');
      const lastNewline = snippet.code.lastIndexOf('\n', vueEmbedIndex);
      const spaces = snippet.code.substring(lastNewline + 1, vueEmbedIndex);
      return snippet.code.replaceAll('%vue_embed%', snippet.vue_embed.replaceAll('\n', `\n${spaces}`));
    },
  },
});
</script>
